<?php

namespace Tests\Unit;

use App\Libraries\CERT\VATSIMUserDetails;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class VATSIMUserDetailsTest extends TestCase
{
    /** @test */
    public function itCanRetrieveXMLCorrectly()
    {
        $this->mock(Client::class, function ($mock) {
            $mock->shouldReceive('get')
                ->andReturn(
                    new Response(200, [], $this->getPratData()),
                    new Response(200, [], $this->getRatData()),
                    new Response(200, [], $this->getStatusIntData()),
                    new Response(200, [], $this->getStatusData())
                );
        });

        $this->assertEquals(4, VATSIMUserDetails::getPreviousRatingsInfo(1300001)->rating);
        $this->assertEquals(555.336, VATSIMUserDetails::getControllingTimes(1300001)->atctime);
        $this->assertEquals('Bloggs', VATSIMUserDetails::getPublicInfo(1300001)->name_last);
        $this->assertEquals('United Kingdom', VATSIMUserDetails::getPublicInfoWithIntRatings(1300001)->division);
    }

    /** @test */
    public function itHandlesHavingNoUserRoot()
    {
        $this->mock(Client::class, function ($mock) {
            $mock->shouldReceive('get')
                ->andReturn(new Response(200, [], '<?xml version="1.0" encoding="utf-8"?>
                <root></root>'));
        });

        $this->assertEquals(null, VATSIMUserDetails::getPublicInfoWithIntRatings(1300001));
    }

    /** @test */
    public function itHandlesErrors()
    {
        $this->mock(Client::class, function ($mock) {
            $mock->shouldReceive('get')
                ->andThrow(new ConnectException('Request timed out', new Request('GET', 'some-url.com')));
        });

        $this->assertEquals(null, VATSIMUserDetails::getPublicInfoWithIntRatings(1300001));
    }

    private function getPratData()
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <root>
                <user cid="1300001">
                    <rating>4</rating>
                </user></root>';
    }

    private function getRatData()
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <root>
                <user cid="1300001">
                    <atctime>555.336</atctime>
                    <pilottime>265.198</pilottime>
                    <S1>194.092</S1>
                    <S2>169.63</S2>
                    <S3>191.615</S3>
                    <C1>0</C1>
                    <C2>0</C2>
                    <C3>0</C3>
                    <I1>0</I1>
                    <I2>0</I2>
                    <I3>0</I3>
                    <SUP>0</SUP>
                    <ADM>0</ADM>
                </user>
                </root>';
    }

    private function getStatusIntData()
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <root>
                <user cid="1300001">
                    <name_last>Bloggs</name_last>
                    <name_first>Joe</name_first>
                    <email>[hidden]@example.org</email>
                    <rating>4</rating>
                    <regdate>2014-06-21 20:02:12</regdate>
                    <pilotrating>1</pilotrating>
                    <country>GB</country>
                    <region>EUR</region>
                    <division>GBR</division>
                    <atctime>555.336</atctime>
                    <pilottime>265.198</pilottime>
                </user>
                </root>';
    }

    private function getStatusData()
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <root>
                <user cid="1300001">
                    <name_last>Bloggs</name_last>
                    <name_first>Joe</name_first>
                    <email>[hidden]@example.org</email>
                    <rating>Senior Student</rating>
                    <regdate>2014-06-21 20:02:12</regdate>
                    <pilotrating>P1</pilotrating>
                    <country>GB</country>
                    <region>Europe</region>
                    <division>United Kingdom</division>
                    <atctime>555.336</atctime>
                    <pilottime>265.198</pilottime>
                </user>
                </root>';
    }
}
