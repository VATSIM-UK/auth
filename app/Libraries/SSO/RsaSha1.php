<?php


namespace App\Libraries\SSO;

use Eher\OAuth\OAuthException;
use Eher\OAuth\RsaSha1 as BaseRsaSha1;

class RsaSha1 extends BaseRsaSha1
{

    private $cert = false;

    public function __construct($cert)
    {
        $this->cert = $cert;
    }

    public function fetch_private_cert(&$request)
    {
        return $this->cert;
    }

    public function fetch_public_cert(&$request)
    {
        throw new OAuthException("fetch_public_cert not implemented");
    }

}
