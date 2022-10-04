<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //
    public function changePassword()
    {
        request()->validate([
            'password' => 'required',
            'newPassword' => 'required',
        ]);

        try{
            $authJson = request()->cookie('auth');
            if ($authJson == null) {
                return response()->json([
                    'status' => '-4',
                    'message' => 'invaild request',
                ], 200);
            }
            
            $authJson = str_replace("'", '"', $authJson);
            $authJson = json_decode($authJson);
    
            if (!isset($authJson->PHPSESSID)||!isset($authJson->uid)) {
                return response()->json([
                    'status' => '-4',
                    'message' => 'invaild request',
                ], 200);
            }
    
            $sessionId = $authJson->PHPSESSID;
            $uid = $authJson->uid;

            $user = DB::select('select id, salt, email, password from users where id = ?', array($uid));
            $user = $user[0];
            if ($user->email) {
                $password = request('password');
                $hashedPw = hash_hmac('sha256', $password, $user->salt);
                if ($hashedPw == $user->password) {
                    $newPassword = request('newPassword');
                    $hashedNewPw = hash_hmac('sha256', $newPassword, $user->salt);

                    $result = DB::update('update users set password = ? where id = ?', array($hashedNewPw, $uid));
                    
                    //$sessionId = request('sessionId');
                    $session = DB::select('select id from sessions where id = ?', array($sessionId));
                    if ($session) {
                        DB::table('sessions')->where('id', '=', $sessionId)->delete();
                    }

                    $PHPSESSID = Str::random();
                    $CSRF_NONCE = Str::random();
                    $expires = time()+3600*24*2;
        
                    $payload = array(
                        'PHPSESSID' => $PHPSESSID,
                        'email' => "guest@guest.com",
                        'expires' => $expires,
                        'username' => "guest",
                        'uid' => 0,
                        'csrf_nonce' => $CSRF_NONCE,
                    );
                    $payloadJson = str_replace('"', "'", json_encode($payload));
                    $result = DB::table('sessions')->insert([
                        'id' => $PHPSESSID,
                        'user_id' => 0,
                        'payload' => $payloadJson,
                        'last_activity' => $expires,
                        'csfr_nonce' => $CSRF_NONCE,
                    ]);
        
                    $authCookie = "auth=".$payloadJson."; expires=".$expires."; HttpOnly; Secure;";
        
                    return response()->json([
                        'status' => '2',
                        'message' => 'guest session',
                        'username' => "guest",
                        'uid' => 0,
                        'flag' => 0,
                    ], 200)
                    ->header('Set-Cookie', $authCookie);
        
        
                    // $expires = time();
                    // $authCookie = "auth=undefined; expires=".$expires."; HttpOnly; Secure;";

                    // return response()->json([
                    //     'status' => '1',
                    //     'message' => 'password changed successfully',
                    // ], 200)
                    // ->header('Set-Cookie', $authCookie);
                }
                return response()->json([
                    'status' => '-2',
                    'message' => 'password not match',
                ], 200);
            }
            return response()->json([
                'status' => '-1',
                'message' => 'user not exist',
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'message' => 'error occur'
            ], 500);
        }
    }

    public function vaildateSession()
    {
        $authJson = request()->cookie('auth');
        if ($authJson == null) {
            $PHPSESSID = Str::random();
            $CSRF_NONCE = Str::random();
            $expires = time()+3600*24*2;

            $payload = array(
                'PHPSESSID' => $PHPSESSID,
                'email' => "guest@guest.com",
                'expires' => $expires,
                'username' => "guest",
                'uid' => 0,
                'csrf_nonce' => $CSRF_NONCE,
            );
            $payloadJson = str_replace('"', "'", json_encode($payload));
            $result = DB::table('sessions')->insert([
                'id' => $PHPSESSID,
                'user_id' => 0,
                'payload' => $payloadJson,
                'last_activity' => $expires,
                'csfr_nonce' => $CSRF_NONCE,
            ]);

            $authCookie = "auth=".$payloadJson."; expires=".$expires."; HttpOnly; Secure;";

            return response()->json([
                'status' => '2',
                'message' => 'guest session',
                'username' => "guest",
                'uid' => 0,
                'flag' => 0,
                'csrf_nonce' => $CSRF_NONCE,
            ], 200)
            ->header('Set-Cookie', $authCookie);
        }

        $authJson = str_replace("'", '"', $authJson);
        $authJson = json_decode($authJson);

        if (!isset($authJson->PHPSESSID)) {
            $PHPSESSID = Str::random();
            $CSRF_NONCE = Str::random();
            $expires = time()+3600*24*2;

            $payload = array(
                'PHPSESSID' => $PHPSESSID,
                'email' => "guest@guest.com",
                'expires' => $expires,
                'username' => "guest",
                'uid' => 0,
                'csrf_nonce' => $CSRF_NONCE,
            );
            $payloadJson = str_replace('"', "'", json_encode($payload));
            $result = DB::table('sessions')->insert([
                'id' => $PHPSESSID,
                'user_id' => 0,
                'payload' => $payloadJson,
                'last_activity' => $expires,
                'csfr_nonce' => $CSRF_NONCE,
            ]);

            $authCookie = "auth=".$payloadJson."; expires=".$expires."; HttpOnly; Secure;";

            return response()->json([
                'status' => '2',
                'message' => 'guest session - invaild request',
                'username' => "guest",
                'uid' => 0,
                'flag' => 0,
                'csrf_nonce' => $CSRF_NONCE,
            ], 200)
            ->header('Set-Cookie', $authCookie);
        }
        
        $isAdmin = false;
        if (isset($authJson->token)) {
            $isAdmin = true;
            $token = $authJson->token;
        }

        $session = $authJson->PHPSESSID;
        $session = DB::select('select user_id, token, last_activity, csfr_nonce from sessions where id = ?', array($session));

        if ($session) {
            $session = $session[0];

            $current_time = time();
            if ($current_time < $session->last_activity) {
                $user = DB::select('select * from users where id = ?', array($session->user_id));
                if ($user) {
                    $user = $user[0];
                    if ($user->flag == 1&&$isAdmin) {
                        if ($token !== $session->token) {
                            $PHPSESSID = Str::random();
                            $CSRF_NONCE = Str::random();
                            $expires = time()+3600*24*2;

                            $payload = array(
                                'PHPSESSID' => $PHPSESSID,
                                'email' => "guest@guest.com",
                                'expires' => $expires,
                                'username' => "guest",
                                'uid' => 0,
                                'csrf_nonce' => $CSRF_NONCE,
                            );
                            $payloadJson = str_replace('"', "'", json_encode($payload));
                            $result = DB::table('sessions')->insert([
                                'id' => $PHPSESSID,
                                'user_id' => 0,
                                'payload' => $payloadJson,
                                'last_activity' => $expires,
                                'csfr_nonce' => $CSRF_NONCE,
                            ]);

                            $authCookie = "auth=".$payloadJson."; expires=".$expires."; HttpOnly; Secure;";

                            return response()->json([
                                'status' => '2',
                                'message' => 'guest session - invaild token',
                                'username' => "guest",
                                'uid' => 0,
                                'flag' => 0,
                                'csrf_nonce' => $CSRF_NONCE,
                            ], 200)
                            ->header('Set-Cookie', $authCookie);
                        }
                    }
                    

                    $uid = $user->id;
                    $username = $user->name;
                    $flag = $user->flag;
                    $csrf_nonce = $session->csfr_nonce;
                    $status = '1';

                    return response()->json([
                        'status' => $status,
                        'message' => 'vaild session',
                        'uid' => $uid,
                        'username' => $username,
                        'flag' => $flag,
                        'csrf_nonce' => $csrf_nonce,
                    ], 200);
                }
            }
            
        }



        $PHPSESSID = Str::random();
        $CSRF_NONCE = Str::random();
        $expires = time()+3600*24*2;

        $payload = array(
            'PHPSESSID' => $PHPSESSID,
            'email' => "guest@guest.com",
            'expires' => $expires,
            'username' => "guest",
            'uid' => 0,
            'csrf_nonce' => $CSRF_NONCE,
        );
        $payloadJson = str_replace('"', "'", json_encode($payload));
        $result = DB::table('sessions')->insert([
            'id' => $PHPSESSID,
            'user_id' => 0,
            'payload' => $payloadJson,
            'last_activity' => $expires,
            'csfr_nonce' => $CSRF_NONCE,
        ]);

        $authCookie = "auth=".$payloadJson."; expires=".$expires."; HttpOnly; Secure;";

        return response()->json([
            'status' => '2',
            'message' => 'guest session  - invaild session',
            'username' => "guest",
            'uid' => 0,
            'flag' => 0,
            'csrf_nonce' => $CSRF_NONCE,
        ], 200)
        ->header('Set-Cookie', $authCookie);
    }
}
