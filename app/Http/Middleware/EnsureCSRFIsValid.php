<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class EnsureCSRFIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // try {
        //     $validResult = $request->validate([
        //         'session' => 'required',
        //         'csrf_nonce' => 'required'
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
        // $csrf_nonce = request('csrf_nonce');
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

        if (!isset($authJson->csrf_nonce)||!isset($authJson->PHPSESSID)) {
            return response()->json([
                'status' => '-4',
                'message' => 'invaild csrf',
            ], 200);
        }

        $csrf_nonce = $authJson->csrf_nonce;
        $session = $authJson->PHPSESSID;

        $session = DB::select('select user_id, csfr_nonce, last_activity from sessions where id = ?', array($session));

        if ($session) {
            $session = $session[0];
            
            $current_time = time();
            if ($current_time > $session->last_activity) {
                return response()->json([
                    'status' => '-1',
                    'message' => 'token expire',
                ], 200);
            }

            if ($csrf_nonce !== $session->csfr_nonce) {
                return response()->json([
                    'status' => '-2',
                    'message' => 'invaild csrf nonce',
                ], 200);
            }

            return $next($request);
        }
        
        return response()->json([
            'status' => '-3',
            'message' => 'invaild session 2',
        ], 200);
    }
}
