# laravel-bitrix24
This is middleware for lavarel that ensures the user has bitrix24 authorization token with every request he/she sends to application.

#Installation
step1
You need to provide hostname, client and secret within your .env file.
B24_HOSTNAME=https://[yourhostname].bitrix24.ru
B24_CLIENT_ID=
B24_CLIENT_SECRET=

step 2
Add Middleware to your app/Http/Kernel.php at middlewareGroups array for 'web'
\bootoffav\laravel\B24\Auth::class

#Usage            
B24 credentials are stored at session, and accessible through either $request object or global session() helper function