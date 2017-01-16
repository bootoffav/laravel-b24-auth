<?php

namespace bootoffav\laravelBitrix24Oauth;

class B24Oauth
{
    public function authorize($request = null, $refresh_token = null)
    {
        if (! session()->has('b24_credentials')) {
            return redirect(config('app.b24_hostname').'/oauth/authorize/?client_id='.config('app.b24_client_id'));
        }
        if (time() > session('b24_credentials')->expires_at) {
            $cred = $this->getCredentials(null, session('b24_credentials')->refresh_token);
            $this->setCredentials($cred);
            return back();
        }
    }

    public function getCredentials($code = null, $refresh_token = null) : string
    {
        if ($code) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,
                'https://oauth.bitrix.info/oauth/token/?grant_type=authorization_code' .
                '&client_id=' . config('app.b24_client_id') .
                '&client_secret=' . config('app.b24_client_secret') .
                '&code=' . $code);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            return curl_exec($ch);
        }

        if ($refresh_token) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,
                'https://oauth.bitrix.info/oauth/token/?grant_type=refresh_token' .
                '&client_id=' . config('app.b24_client_id') .
                '&client_secret=' . config('app.b24_client_secret') .
                '&refresh_token=' . $refresh_token);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            return curl_exec($ch);
        }
    }

    public function setCredentials(string $credentials)
    {
        $credentials = json_decode($credentials);
        $credentials->expires_at = time() + $credentials->expires_in;
        session()->put('b24_credentials', $credentials);
    }
}
