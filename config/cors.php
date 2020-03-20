<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cors Defaults
    |--------------------------------------------------------------------------
    |
    | The following sites will exclude cors, they maybe use oauth2.0 to identify.
    |
    */

    //完整的url,逗号分割: http://localhost:8080,http...
    'domain' => env('CORS_WHITELIST', env('APP_URL')),
];
