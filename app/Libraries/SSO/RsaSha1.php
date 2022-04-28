<?php

namespace App\Libraries\SSO;

use Eher\OAuth\RsaSha1 as BaseRsaSha1;
use League\OAuth2\Server\Exception\OAuthServerException;

class RsaSha1 extends BaseRsaSha1
{
    private $cert = false;

    public function __construct($cert)
    {
        $this->cert = $cert;
    }

    /**
     * Fetch the private RSA certificate.
     *
     * @param $request
     * @return string
     */
    public function fetch_private_cert(&$request)
    {
        return $this->cert;
    }

    /**
     * Fetch the public RSA certificate (we do not use this functionality).
     *
     * @param $request
     *
     * @throws OAuthServerException
     */
    public function fetch_public_cert(&$request)
    {
        throw new OAuthServerException('fetch_public_cert not implemented');
    }
}
