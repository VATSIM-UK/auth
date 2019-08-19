<?php

namespace App\Libraries\SSO;

use Closure;
use Eher\OAuth\Consumer;
use Eher\OAuth\HmacSha1;
use Eher\OAuth\Request;
use Illuminate\Container\Container;

class VATSIMSSO
{

    /*
     * Location of the VATSIM SSO system
     * Set in __construct
     */
    /**
     * The container instance.
     *
     * @var Container
     */
    protected $container;

    /*
     * Location for all OAuth requests
     */
    private $base = '';

    /*
     * Location for all login token requests
     */
    private $loc_api = 'api/';

    /*
     * Location to query for all user data requests (upon return of user login)
     */
    private $loc_token = 'login_token/';

    /*
     * Location to redirect the user to once we have generated a token
     */
    private $loc_return = 'login_return/';

    /*
     * Format of the data returned by SSO, default json
     * Set in responseFormat method
     */
    private $loc_login = 'auth/pre_login/?oauth_token=';

    /*
     * cURL timeout (seconds) for all requests
     */
    private $format = 'json';

    /*
     * Holds the details of the most recent error in this class
     */
    private $timeout = 10;

    /*
     * The signing method being used to encrypt your request signature.
     * Set the 'signature' method
     */
    private $error = array(
        'type' => false,
        'message' => false,
        'code' => false
    );

    /*
     * A request token genereted by (or saved to) the class
     */
    private $signature = false;

    /*
     * Consumer credentials, instance of OAuthConsumer
     */
    private $token = false;
    private $consumer = false;

    /**
     * Configure instance with credentials
     */
    public function __construct()
    {
        $this->container = new Container;
        $this->base = config('vatsimsso.base');

        // Store consumer credentials
        $this->consumer = new Consumer(config('vatsimsso.key'), config('vatsimsso.secret'));

        $this->signature(config('vatsimsso.method'), config('vatsimsso.cert'));
    }


    /*
     * SSO Base Authentication Methods
     */

    /**
     *
     * Attempt redirection to SSO login
     *
     * @param $returnUrl
     * @param $success
     * @param null $error
     * @return bool|mixed
     */
    public function login($returnUrl, $success, $error = null)
    {
        if ($token = $this->requestToken($returnUrl, false, false)) {
            return $this->callResponse($success, [
                (string)$token->token->oauth_token,
                (string)$token->token->oauth_token_secret,
                $this->sendToVatsim()
            ]);
        }

        if (is_null($error))
            return false;
        return $this->callResponse($error, [$this->getError()]);
    }

    /**
     * Validate user returning from SSO
     *
     * @param $key
     * @param $secret
     * @param $verifier
     * @param $success
     * @param null $error
     * @return bool|mixed
     */
    public function validate($key, $secret, $verifier, $success, $error = null)
    {
        if ($request = $this->checkLogin($key, $secret, $verifier)) {
            return $this->callResponse($success, [
                $request->user,
                $request->request
            ]);
        } else {
            if (is_null($error))
                return false;
            return $this->callResponse($error, [$this->getError()]);
        }
    }


    /**
     * Request a login token from VATSIM (required to send someone for an SSO login)
     *
     * @param string $return_url URL for VATSIM to return memers to after login
     * @param boolean $allow_sus true to allow suspended VATSIM accounts to log in
     * @param boolean $allow_ina true to allow inactive VATSIM accounts to log in
     * @return object|boolean
     */
    public function requestToken($return_url = false, $allow_sus = false, $allow_ina = false)
    {
        // if the return URL isn't specified, assume this file (though don't consider GET data)
        if (!$return_url) {
            // using https or http?
            $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https://' : 'http://';
            // the current URL
            $return_url = $http . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
        }

        $tokenUrl = $this->base . $this->loc_api . $this->loc_token . $this->format . '/';

        // generate a token request from the consumer details
        $req = Request::from_consumer_and_token($this->consumer, false, "POST", $tokenUrl, array(
            'oauth_callback' => (String)$return_url,
            'oauth_allow_suspended' => ($allow_sus) ? true : false,
            'oauth_allow_inactive' => ($allow_ina) ? true : false
        ));
        // sign the request using the specified signature/encryption method (set in this class)
        $req->sign_request($this->signature, $this->consumer, false);

        $response = $this->curlRequest($tokenUrl, $req->to_postdata());

        if ($response) {
            // convert using our response format (depending upon user preference)
            $sso = $this->responseFormat($response);

            // did VATSIM return a successful result?
            if ($sso->request->result == 'success') {

                // this parameter is required by 1.0a spec
                if ($sso->token->oauth_callback_confirmed == 'true') {
                    // store the token data saved
                    $this->token = new Consumer($sso->token->oauth_token, $sso->token->oauth_token_secret);
                    // return the full object to the user
                    return $sso;
                } else {
                    // no callback_confirmed parameter
                    $this->error = [
                        'type' => 'callback_confirm',
                        'code' => false,
                        'message' => 'Callback confirm flag missing - protocol mismatch'
                    ];
                    return false;
                }

            } else {

                // oauth returned a failed request, store the error details
                $this->error = [
                    'type' => 'oauth_response',
                    'code' => false,
                    'message' => $sso->request->message
                ];

                return false;
            }
        } else {
            // cURL response failed
            return false;
        }

    }

    /**
     * Obtains a user's login details from a token key and secret
     *
     * @param string $tokenKey The token key provided by VATSIM
     * @param secret $tokenSecret The secret associated with the token
     * @return object|false         false if error, otherwise returns user details
     */
    public function checkLogin($tokenKey, $tokenSecret, $tokenVerifier)
    {

        $this->token = new Consumer($tokenKey, $tokenSecret);

        // the location to send a cURL request to to obtain this user's details
        $returnUrl = $this->base . $this->loc_api . $this->loc_return . $this->format . '/';

        // generate a token request call using post data
        $req = Request::from_consumer_and_token($this->consumer, $this->token, "POST", $returnUrl, array(
            'oauth_token' => $tokenKey,
            'oauth_verifier' => $tokenVerifier
        ));

        // sign the request using the specified signature/encryption method (set in this class)
        $req->sign_request($this->signature, $this->consumer, $this->token);

        // post the details to VATSIM and obtain the result
        $response = $this->curlRequest($returnUrl, $req->to_postdata());

        if ($response) {
            // convert using our response format (depending upon user preference)
            $sso = $this->responseFormat($response);

            // did VATSIM return a successful result?
            if ($sso->request->result == 'success') {

                // one time use of tokens only, token no longer valid
                $this->token = false;

                // return the full object to the user
                return $sso;
            } else {

                // oauth returned a failed request, store the error details
                $this->error = array(
                    'type' => 'oauth_response',
                    'code' => false,
                    'message' => $sso->request->message
                );

                return false;

            }

        } else {
            // cURL response failed
            return false;
        }

    }


    /*
     * Utilities
     */

    /**
     * Perform a (post) cURL request
     *
     * @param type $url Destination of request
     * @param type $bodu Query string of data to be posted
     * @return boolean              true if able to make request
     */
    private function curlRequest($url, $body)
    {
        // using cURL to post the request to VATSIM
        $ch = curl_init();

        // configure the post request to VATSIM
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url, // the url to make the request to
            CURLOPT_RETURNTRANSFER => 1, // do not output the returned data to the user
            CURLOPT_TIMEOUT => $this->timeout, // time out the request after this number of seconds
            CURLOPT_POST => 1, // we are sending this via post
            CURLOPT_POSTFIELDS => $body // a query string to be posted (key1=value1&key2=value2)
        ));

        // perform the request
        $response = curl_exec($ch);

        // request failed?
        if (!$response) {
            $this->error = array(
                'type' => 'curl_response',
                'code' => curl_errno($ch),
                'message' => curl_error($ch)
            );

            return false;

        } else {

            return $response;

        }

    }

    private function responseFormat($response)
    {
        return json_decode($response);
    }

    /**
     * Redirect the user to VATSIM to log in/confirm login
     *
     * @return boolean              false if failed
     */
    public function sendToVatsim()
    {
        // a token must have been returned to redirect this user
        if (!$this->token) {
            return false;
        }

        // redirect to the SSO login location, appending the token
        return $this->base . $this->loc_login . $this->token->key;
    }

    /**
     * Set the signing method to be used to encrypt request signature.
     *
     * @param string $signature Signature encryption method: RSA|HMAC
     * @param string $private_key openssl RSA private key (only needed if using RSA)
     * @return boolean                  true if able to use this signing type
     */
    public function signature($signature, $private_key = false)
    {

        $signature = strtoupper($signature);

        // RSA-SHA1 public key/private key encryption
        if ($signature == 'RSA' || $signature == 'RSA-SHA1') {

            // private key must be provided
            if (!$private_key) {
                return false;
            }

            // signature method set to RSA-SHA1 using this private key (interacts with OAuth class)
            $this->signature = new RsaSha1($private_key);

            return true;

        } elseif ($signature == 'HMAC' || $signature == 'HMAC-SHA1') {

            // signature method set to HMAC-SHA1 - no private key
            $this->signature = new HmacSha1;

            return true;
        } else {
            // signature method was not recognised
            return false;
        }
    }


    protected function callResponse($callback, $parameters)
    {
        if ($callback instanceof Closure) {
            return call_user_func_array($callback, $parameters);
        } elseif (is_string($callback)) {
            return $this->callClassBasedResponse($callback, $parameters);
        }
    }

    protected function callClassBasedResponse($callback, $parameters)
    {
        list($class, $method) = explode('@', $callback);
        return call_user_func_array(array($this->container->make($class), $method), $parameters);
    }

    /*
     * Getters
     */

    /**
     * Obtain the last generated error from this class
     *
     * @return array                Array of the latest error
     */
    public function getError()
    {
        return $this->error;
    }

}
