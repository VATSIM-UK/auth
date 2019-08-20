<?php

return array(

    /*
    * The location of the VATSIM OAuth interface
    */
    'base' => env('VATSIM_SSO_BASE', 'https://cert.vatsim.net/sso/'),

    /*
     * The consumer key for your organisation (provided by VATSIM)
     */
    'key' => env('VATSIM_SSO_KEY'),

    /*
    * The secret key for your organisation (provided by VATSIM)
    * Do not give this to anyone else or display it to your users. It must be kept server-side
    */
    'secret' => env('VATSIM_SSO_SECRET'),

    /*
     * The URL users will be redirected to after they log in, this should
     * be on the same server as the request
     */
    'return' => '',
    /*
     * The signing method you are using to encrypt your request signature.
     * Different options must be enabled on your account at VATSIM.
     * Options: RSA / HMAC
     */
    'method' => 'RSA',

    /*
     * Your RSA **PRIVATE** key
     * If you are not using RSA, this value can be anything (or not set)
     */
    'cert' => str_replace('\n', "\n", env('VATSIM_SSO_CERTIFCATE', '')),

);
