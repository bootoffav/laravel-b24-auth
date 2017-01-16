<?php

namespace bootoffav\laravelBitrix24Oauth;

class B24Oauth
{
    public function handle($request, \Closure $next)
    {
        if (! session()->has('b24_credentials') or time() > session()->get('b24_credentials')->expires_at) {
            return $this->authorize();
        }
        
        return $next($request);
    }

   private function authorize()
    {
        if (! session()->has('b24_credentials')) {
            return redirect(env('B24_HOSTNAME').'/oauth/authorize/?client_id='.env('B24_CLIENT_ID'));
        }
        if (time() > session('b24_credentials')->expires_at) {
            $cred = $this->getCredentials(null, session('b24_credentials')->refresh_token);
            $this->setCredentials($cred);
            return back();
        }
    }

    static public function getCredentials($code = null, $refresh_token = null) : string
    {
        if ($code) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,
                'https://oauth.bitrix.info/oauth/token/?grant_type=authorization_code' .
                '&client_id=' . env('B24_CLIENT_ID') .
                '&client_secret=' . env('B24_CLIENT_SECRET') .
                '&code=' . $code);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            return curl_exec($ch);
        }

        if ($refresh_token) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,
                'https://oauth.bitrix.info/oauth/token/?grant_type=refresh_token' .
                '&client_id=' . env('B24_CLIENT_ID') .
                '&client_secret=' . env('B24_CLIENT_SECRET') .
                '&refresh_token=' . $refresh_token);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            return curl_exec($ch);
        }
    }

    private function setCredentials(string $credentials)
    {
        $credentials = json_decode($credentials);
        $credentials->expires_at = time() + $credentials->expires_in;
        session()->put('b24_credentials', $credentials);
    }
}
