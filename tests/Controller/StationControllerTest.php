<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


class StationControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/stations');
        self::assertResponseStatusCodeSame(200);
    }

}
