<?php

namespace App\Tests\Controller;

use App\Service\MPC;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


class IndexControllerTest extends WebTestCase
{

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');
        // player does not play - redirect to stations
        self::assertResponseStatusCodeSame(302);
    }

    public function testIndexWhenPlaying(): void
    {
        $client = static::createClient();

        // getPlaylistFileContent is used by StationRepository::getCurrent() to find the active station
        $mockMpc = $this->createStub(MPC::class);
        $mockMpc->method('isPlaying')->willReturn(true);
        $mockMpc->method('getState')->willReturn('[playing] Test Station');
        $mockMpc->method('getVolume')->willReturn(50);
        $mockMpc->method('getPlaylistFileContent')->willReturn('https://stream.radio.com?st=testUpdateStation');
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/');
        self::assertResponseStatusCodeSame(200);
    }

    public function testIndexWhenExceptionThrown(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createStub(MPC::class);
        $mockMpc->method('isPlaying')->willThrowException(new \Exception('mpc error'));
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/');
        // index() catches Exception and redirects to stations with error message
        self::assertResponseStatusCodeSame(302);
        self::assertStringContainsString('/stations?e=', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testVolumeMute(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createMock(MPC::class);
        $mockMpc->expects($this->once())->method('setVolume')->with(0);
        $mockMpc->method('isPlaying')->willReturn(false);
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/volume/mute');
        self::assertResponseRedirects('/stations');
    }

    public function testVolumeDown(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createMock(MPC::class);
        $mockMpc->method('getVolume')->willReturn(50);
        $mockMpc->expects($this->once())->method('setVolume')->with(45);
        $mockMpc->method('isPlaying')->willReturn(false);
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/volume/down');
        self::assertResponseRedirects('/stations');
    }

    public function testVolumeDownClampsAtZero(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createMock(MPC::class);
        $mockMpc->method('getVolume')->willReturn(2);
        $mockMpc->expects($this->once())->method('setVolume')->with(0);
        $mockMpc->method('isPlaying')->willReturn(false);
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/volume/down');
        self::assertResponseRedirects('/stations');
    }

    public function testVolumeUp(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createMock(MPC::class);
        $mockMpc->method('getVolume')->willReturn(50);
        $mockMpc->expects($this->once())->method('setVolume')->with(55);
        $mockMpc->method('isPlaying')->willReturn(false);
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/volume/up');
        self::assertResponseRedirects('/stations');
    }

    public function testVolumeUpClampsAt100(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createMock(MPC::class);
        $mockMpc->method('getVolume')->willReturn(98);
        $mockMpc->expects($this->once())->method('setVolume')->with(100);
        $mockMpc->method('isPlaying')->willReturn(false);
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/volume/up');
        self::assertResponseRedirects('/stations');
    }

    public function testVolumeFull(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createMock(MPC::class);
        $mockMpc->expects($this->once())->method('setVolume')->with(100);
        $mockMpc->method('isPlaying')->willReturn(false);
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/volume/full');
        self::assertResponseRedirects('/stations');
    }

    public function testVolumeSet(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createMock(MPC::class);
        $mockMpc->expects($this->once())->method('setVolume')->with(42);
        $mockMpc->method('isPlaying')->willReturn(false);
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/volume/42');
        self::assertResponseRedirects('/stations');
    }

    public function testVolumeSetOutOfRangeSkipsSetVolume(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createMock(MPC::class);
        $mockMpc->expects($this->never())->method('setVolume');
        $mockMpc->method('isPlaying')->willReturn(false);
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/volume/150');
        self::assertResponseRedirects('/stations');
    }

}
