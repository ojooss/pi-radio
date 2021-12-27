<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


class IndexControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');
        // player does not play - redirect to stations
        self::assertResponseStatusCodeSame(302);
    }

}
