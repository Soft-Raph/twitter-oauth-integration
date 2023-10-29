<?php

namespace App\Api\V1\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class TwitterIntegrationController extends Controller {

    public $client_id;
    public $redirect_uri;
    public $client_secret;

    /**
     *
     */
    public function __construct()
    {
        $this->redirect_uri = route('user_bot');
        $this->client_id = env('CLIENT_ID');
        $this->client_secret = env('CLIENT_SECRET');
    }

    /**
     * @OA\Get(
     *     path="/subscribe/user/channel",
     *     summary="Subscribe user to channel",
     *     tags={"Subscription"},
     *     @OA\Response(
     *         response=200,
     *         description="User subscribed to the channel successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */

    public function subscribe_channel(){

        $response = 'https://twitter.com/i/oauth2/authorize?response_type=code&'.'client_id='.$this->client_id.'&redirect_uri='
            .$this->redirect_uri.'&scope=tweet.read,users.read,follows.read,offline.access&state=state&code_challenge=challenge&code_challenge_method=plain';
        $url = str_replace(',','%20',$response);
        return ResponseHelper::success(['url' => $url], 'Subscribing User to a channel successful', '200');
    }


    /**
     * @OA\Get(
     *     path="/subscribe/user/bot",
     *     summary="Subscribe user to bot",
     *     tags={"Subscription"},
     *     @OA\Response(
     *         response=200,
     *         description="User subscribed to the bot successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function subscribe_bot(Request $request){

        $tokenResponse =Http::withHeaders([
                   'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->client_id .':'. $this->client_secret),
        ])->post('https://api.twitter.com/2/oauth2/token',[
            'grant_type'=>'authorization_code',
            'client_id'=>$this->client_id,
            'code'=>$request->get('code'),
            'redirect_uri'=>$this->redirect_uri,
            'code_verifier'=> 'challenge',
        ])->json();
        if (!isset($tokenResponse['access_token'])) {
            return ResponseHelper::error('405','Sorry an error occur please try again');
        }
       $access_token = $tokenResponse['access_token'];

       $UserResponse =Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' .$access_token,
        ])->get('https://api.twitter.com/2/users/me',)->json();

        if (!isset($UserResponse['data']['username'])) {
            return ResponseHelper::error('405','Sorry an error occur please try again');
        }
       $user = DB::table('users')
            ->where('twitter_username', $UserResponse['data']['username'])
            ->first();
        if ($user){
            DB::table('users')
                ->where('twitter_username', $UserResponse['data']['username'])
                ->update([
                    'twitter_access_token' => $access_token,
                ]);
        } else {
            DB::table('users')->insert([
                'twitter_username' => $UserResponse['data']['username'],
                'twitter_id' => $UserResponse['data']['id'],
                'twitter_access_token' => $access_token,
            ]);
        }

        return ResponseHelper::success($UserResponse,'Subscribing User to a Bot successful','200');
    }

    /**
     * @OA\Post(
     *     path="/subscribe/user/message",
     *     summary="Subscribe user to messages",
     *     tags={"Subscription"},
     *     @OA\Response(
     *         response=200,
     *         description="User subscribed to messages successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */

    public function subscribe_message(Request $request){
        $userId = $request->get('twitter_id');
        $text = $request->get('text');
        $user = DB::table('users')
            ->where('twitter_id', $request->get('twitter_id'))
            ->first();
        if (!$user){
            return ResponseHelper::error('403','Sorry the user with the Id does not exit');
        }
        $messageUser =Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' .$user->twitter_access_token,
        ])->get('https://api.twitter.com/2/dm_conversations/with/'.$userId.'/messages', [
            'text'=>$text,
        ])->json();
        // need the success response to know how to handle the success response here
        return ResponseHelper::success($messageUser, 'Message sent successfully', '200');
    }

    /**
     * @OA\Post(
     *     path="/webhook",
     *     summary="Handle incoming webhooks",
     *     tags={"Webhook"},
     *     @OA\Response(
     *         response=200,
     *         description="Webhook successfully processed"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */

    public function webhook(){
        //
    }
}
