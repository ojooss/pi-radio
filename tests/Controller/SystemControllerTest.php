<?php

namespace App\Tests\Controller;

use App\Exception\SystemCallException;
use App\Service\MPC;
use App\Service\System;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


class SystemControllerTest extends WebTestCase
{

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, 'system');
        self::assertResponseStatusCodeSame(200);
    }

    public function testIndexWithResultMessage(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/system?msg=Operation+successful');
        self::assertResponseStatusCodeSame(200);
    }

    public function testResetMpdSuccess(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createStub(MPC::class);
        $mockMpc->method('startMpd')->willReturn('mpd started');
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/system/reset/mpd');
        self::assertResponseStatusCodeSame(302);
        self::assertStringContainsString('/system', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testResetMpdSystemCallException(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createStub(MPC::class);
        $mockMpc->method('startMpd')->willThrowException(
            new SystemCallException('mpd start failed', ['error output line'])
        );
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/system/reset/mpd');
        self::assertResponseStatusCodeSame(302);
        $location = (string) $client->getResponse()->headers->get('Location');
        self::assertStringContainsString('/system', $location);
        self::assertStringContainsString('e=', $location);
    }

    public function testCallSystemCommandExceptionRendersErrorDiv(): void
    {
        $client = static::createClient();

        $stubSystem = $this->createStub(System::class);
        $stubSystem->method('call')->willThrowException(new \Exception('system unavailable'));
        static::getContainer()->set(System::class, $stubSystem);

        $client->request(Request::METHOD_GET, '/system');
        self::assertResponseStatusCodeSame(200);
        self::assertStringContainsString(
            'bg-danger',
            (string) $client->getResponse()->getContent()
        );
        self::assertStringContainsString(
            'system unavailable',
            (string) $client->getResponse()->getContent()
        );
    }

    public function testResetMpdGenericException(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createStub(MPC::class);
        $mockMpc->method('startMpd')->willThrowException(new \Exception('unexpected error'));
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/system/reset/mpd');
        self::assertResponseStatusCodeSame(302);
        $location = (string) $client->getResponse()->headers->get('Location');
        self::assertStringContainsString('/system', $location);
        self::assertStringContainsString('e=', $location);
    }

}
