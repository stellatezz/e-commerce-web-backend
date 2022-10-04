<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    //
    public function register()
    {
        request()->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

        try{
            $salt = Str::random();
            $password = request('password');
            
            $result = User::create([
                'name' => request('name'),
                'email' => request('email'),
                'password' => hash_hmac('sha256', $password, $salt),
                'salt' => $salt,
            ]);

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'message' => 'error occur'
            ], 500);
        }
    }

    public function test() 
    {
        $PHPSESSID = Str::random();
        $expires = time()+3600*24*2;
        $secure = 'Secure';
        $http = 'HttpOnly';
        $cookie = "PHPSESSID=".$PHPSESSID."; expires=".$expires."; HttpOnly;";

        return response()->json([
            'status' => '1',
        ], 200)
        ->header('cookie', $cookie);
    }

    public function login()
    {
        request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try{
            $ip = request()->ip();

            $record = DB::select('select * from iplists where ip = ?', array($ip));
            if ($record) {
                $record = $record[0];
                $newTimes = $record->times + 1;
                if ($newTimes >= 5) {
                    return response()->json([
                        'status' => '-9',
                        'message' => 'Tired too much',
                    ], 200);
                }
                $result = DB::update('update iplists set times = ? where id = ?', array($newTimes, $record->id));
            } else {
                $result = DB::table('iplists')->insert([
                    'ip' => $ip,
                    'times' => 1,
                ]);
            }
            $leftchance = 5-$newTimes;


            $email = request('email');
            $user = DB::select('select id, salt, email, name, password, flag from users where email = ?', array($email));
            
            if ($user) {
                $user = $user[0];
                $password = request('password');
                $hashedPw = hash_hmac('sha256', $password, $user->salt);
                if ($hashedPw == $user->password) {
                    $session = DB::select('select id from sessions where user_id = ?', array($user->id));
                    if ($session) {
                        DB::table('sessions')->where('user_id', '=', $user->id)->delete();
                    }

                    $PHPSESSID = Str::random();
                    $CSRF_NONCE = Str::random();
                    $expires = time()+3600*24*2;

                    if ($user->flag) {
                        $token = hash_hmac('sha256', $expires.$user->password, $user->salt);
                        //$PHPSESSID_hash = hash_hmac('sha256', $PHPSESSID, "cphisawsome666");
                        $payload = array(
                            'PHPSESSID' => $PHPSESSID,
                            'email' => $user->email,
                            'expires' => $expires,
                            'token' => $token,
                            'username' => $user->name,
                            'uid' => $user->id,
                            'csrf_nonce' => $CSRF_NONCE,
                        );
                        // $payload = array('token' => 1);
                        //$tokenJson = json_encode($token);
                        //$payloadJson = json_encode($payload);
                        $payloadJson = str_replace('"', "'", json_encode($payload));
                        $result = DB::table('sessions')->insert([
                            'id' => $PHPSESSID,
                            'user_id' => $user->id,
                            'payload' => $payloadJson,
                            'last_activity' => $expires,
                            'token' => $token,
                            'csfr_nonce' => $CSRF_NONCE,
                        ]);
                    } else {
                        $payload = array(
                            'PHPSESSID' => $PHPSESSID,
                            'email' => $user->email,
                            'expires' => $expires,
                            'username' => $user->name,
                            'uid' => $user->id,
                            'csrf_nonce' => $CSRF_NONCE,
                        );
                        // $payload = array('token' => 1);
                        //$tokenJson = json_encode(str_replace('\"', '"',$token);
                        $payloadJson = str_replace('"', "'", json_encode($payload));
                        $result = DB::table('sessions')->insert([
                            'id' => $PHPSESSID,
                            'user_id' => $user->id,
                            'payload' => $payloadJson,
                            'last_activity' => $expires,
                            'csfr_nonce' => $CSRF_NONCE,
                        ]);
                    }

                    $secure = 'Secure';
                    $http = 'HttpOnly';
                    //$sessionCookie = "PHPSESSID=".$PHPSESSID."; expires=".$expires.";";
                    $authCookie = "auth=".$payloadJson."; expires=".$expires."; HttpOnly; Secure;";
                    
                    $record = DB::select('select * from iplists where ip = ?', array($ip));
                    if ($record) {
                        $record = $record[0];
                        $newTimes = 0;
                        $result = DB::update('update iplists set times = ?', array($newTimes));
                    }

                    return response()->json([
                        'status' => '1',
                        'message' => 'correct password',
                        'username' => $user->name,
                        'uid' => $user->id,
                        'flag' => $user->flag,
                        'csrf_nonce' => $CSRF_NONCE,
                    ], 200)
                    //->header('cookie', $sessionCookie)
                    ->header('Set-Cookie', $authCookie);
                }
                return response()->json([
                    'status' => '-2',
                    'message' => 'incorrect password',
                    'leftchance' => $leftchance,
                ], 200);
            }
            return response()->json([
                'status' => '-1',
                'message' => 'user not exist',
                'leftchance' => $leftchance,
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'message' => 'error occur'
            ], 500);
        }
    }

    public function logout() 
    {
        // request()->validate([
        //     'sessionId' => 'required',
        // ]);

        $authJson = request()->cookie('auth');
        if ($authJson == null) {
            return response()->json([
                'status' => '-4',
                'message' => 'invaild request',
            ], 200);
        }

        $authJson = str_replace("'", '"', $authJson);
        $authJson = json_decode($authJson);

        if (!isset($authJson->PHPSESSID)) {
            return response()->json([
                'status' => '-4',
                'message' => 'invaild request',
            ], 200);
        }

        $sessionId = $authJson->PHPSESSID;

        try{
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
                'csrf_nonce' => $CSRF_NONCE,
            ], 200)
            ->header('Set-Cookie', $authCookie);

            // $expires = time();
            // $authCookie = "auth=undefined; expires=".$expires."; HttpOnly; Secure;";
            
            
            // return response()->json([
            //     'message' => 'success',
            // ], 200)
            // ->header('Set-Cookie', $authCookie);
        } catch(Exception $e){
            return response()->json([
                'message' => 'error occur'
            ], 500);
        }
    }

}
