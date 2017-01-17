<?php

namespace bootoffav\laravel\B24;

class Auth
{
    public function handle($request, \Closure $next)
    {
        if ($request['code']) {
            $cred = $this->getCredentials(
                'https://oauth.bitrix.info/oauth/token/?grant_type=authorization_code' .
                '&client_id=' . env('B24_CLIENT_ID') .
                '&client_secret=' . env('B24_CLIENT_SECRET') .
                '&code=' . $request['code']
            );
            $this->setCredentials($cred);
        }

        if (! $request->session()->has('b24_credentials')) {
            return redirect(env('B24_HOSTNAME').'/oauth/authorize/?client_id='.env('B24_CLIENT_ID'));
        }

        if (time() > $request->session()->get('b24_credentials')->expires_at) {
            $cred = $this->getCredentials(
                'https://oauth.bitrix.info/oauth/token/?grant_type=refresh_token' .
                '&client_id=' . env('B24_CLIENT_ID') .
                '&client_secret=' . env('B24_CLIENT_SECRET') .
                '&refresh_token=' . session('b24_credentials')->refresh_token
            );
            $this->setCredentials($cred);
        }
        
        return $next($request);
    }

    private function getCredentials($request_str) : string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return curl_exec($ch);
    }

    private function setCredentials(string $credentials)
    {
        $credentials = json_decode($credentials);
        $credentials->expires_at = time() + $credentials->expires_in;
        session()->put('b24_credentials', $credentials);
    }
}
