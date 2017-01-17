<?php

namespace bootoffav\laravel\B24;

class Auth
{
    /**
        * Handle an incoming request.
        *
        * @param  \Illuminate\Http\Request  $request
        * @param  \Closure  $next
        * @param  string|null  $guard
        * @return mixed
    */
    public function handle($request, \Closure $next)
    {
        if (! session()->has('b24_credentials')) {
            return redirect(env('B24_HOSTNAME').'/oauth/authorize/?client_id='.env('B24_CLIENT_ID'));
        }

        if (time() > session('b24_credentials')->expires_at) {
            $cred = $this->getCredentials(
                'https://oauth.bitrix.info/oauth/token/?grant_type=refresh_token' .
                '&client_id=' . env('B24_CLIENT_ID') .
                '&client_secret=' . env('B24_CLIENT_SECRET') .
                '&refresh_token=' . session('b24_credentials')->refresh_token
            );
            $this->setCredentials($cred);
        
            return back();
        }
        
        return $next($request);
    }

    public function getCredentials($request_string) : string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return curl_exec($ch);
    }

    /**
     * @param string $credentials return value from $this->getCredentials
     * @return  void
     */
    private function setCredentials(string $credentials)
    {
        $credentials = json_decode($credentials);
        $credentials->expires_at = time() + $credentials->expires_in;
        session()->put('b24_credentials', $credentials);
    }
}
