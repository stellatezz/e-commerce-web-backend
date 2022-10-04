<?php
 
namespace App\Http\Middleware;

use Illuminate\Support\Facades\DB;
use Closure;
 
class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // try {
        //     $validResult = $request->validate([
        //         'token' => 'required',
        //         'session' => 'required'
        //     ]);
        // } catch (Exception $exception) {
        //     // $validatorInstance = $exception->validator;
        //     // $errorMessageData = $validationInstance->getMessageBag();
        //     // $errorMessages = $errorMessageData->getMessages();
        //     return response()->json([
        //         'status' => '0',
        //         'message' => 'error',
        //     ], 200);
        // }

        
        // $token = request('token');
        // $session = request('session');

        $authJson = request()->cookie('auth');
        if ($authJson == null) {
            return response()->json([
                'status' => '-4',
                'message' => 'invaild auth',
            ], 200);
        }

        $authJson = str_replace("'", '"', $authJson);
        $authJson = json_decode($authJson);

        if (!isset($authJson->token)||!isset($authJson->PHPSESSID)) {
            return response()->json([
                'status' => '-4',
                'message' => 'invaild token',
            ], 200);
        }

        $token = $authJson->token;
        $session = $authJson->PHPSESSID;


        $session = DB::select('select user_id, token, last_activity from sessions where id = ?', array($session));

        if ($session) {
            $session = $session[0];
            

            $user = DB::select('select flag from users where id = ?', array($session->user_id));
            if ($user) {
                $user = $user[0];
                if ($user->flag !== 1) {
                    return response()->json([
                        'status' => '-4',
                        'message' => 'invaild action',
                    ], 200);
                }
            }
            
            $current_time = time();
            if ($current_time > $session->last_activity) {
                return response()->json([
                    'status' => '-1',
                    'message' => 'token expire',
                ], 200);
            }

            if ($token !== $session->token) {
                return response()->json([
                    'status' => '-2',
                    'message' => 'invaild token',
                ], 200);
            }

            return $next($request);
        }

        return response()->json([
            'status' => '-3',
            'message' => 'invaild session 1',
        ], 200);
    }
}