<?php


namespace App\Libraries\CERT;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class VATSIMUserDetails
{
    /* Information on previous ratings (mainly for I1+ / ADM) */
    private static $idstatusprat = "https://cert.vatsim.net/vatsimnet/idstatusprat.php?cid=";

    /* Information on a user's controlling times */
    private static $idstatusrat = "https://cert.vatsim.net/vatsimnet/idstatusprat.php?cid=";

    /* General information for a VATSIM user */
    private static $idstatusint = "https://cert.vatsim.net/vatsimnet/idstatusprat.php?cid=";

    /* Same as above, but with ratings converted into textual representations */
    private static $idstatus = "https://cert.vatsim.net/vatsimnet/idstatusprat.php?cid=";

    public static function getPreviousRatingsInfo($cid)
    {
        return self::callMethod(self::$idstatusprat, $cid);
    }

    public static function getControllingTimes($cid)
    {
        return self::callMethod(self::$idstatusrat, $cid);
    }

    public static function getPublicInfoWithIntRatings($cid)
    {
        return self::callMethod(self::$idstatusint, $cid);
    }

    public static function getPublicInfo($cid)
    {
        return self::callMethod(self::$idstatus, $cid);
    }

    private static function callMethod($url, int $cid)
    {
        $client = new Client();

        try {
            $response = $client->get($url . $cid);

            $xml = new \SimpleXMLElement($response->getBody()->getContents());

            if(!$xml->user){
                //TODO: Log exception
                return null;
            }
        } catch (ConnectException $e){
            //TODO: Log exception
            return null;
        }

        $values = json_decode(json_encode($xml->user), false);
        $values->cid = $values->{"@attributes"}->cid;
        unset($values->{"@attributes"});
        return $values;
    }

}
