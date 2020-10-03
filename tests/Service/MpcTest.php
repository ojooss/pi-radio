<?php

namespace App\Tests\Service;

use App\Entity\Station;
use App\Exception\MpcException;
use App\Exception\SystemCallException;
use App\Service\MPC;
use App\Service\System;
use Doctrine\Persistence\ObjectManager;
use Mockery;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Throwable;

class MpcTest extends KernelTestCase
{

    /**
     * @var ObjectManager|null
     */
    private ObjectManager $entityManager;

    final function setUp(): void
    {
        parent::setUp();

        static::bootKernel();
        
        $this->entityManager = parent::$kernel
            ->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    final function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * @throws SystemCallException
     * @throws Throwable
     * @noinspection DuplicatedCode
     */
    final function testPlay(): void
    {
        $stations = $this
            ->entityManager
            ->getRepository(Station::class)
            ->findBy(['name' => 'My-Test-Station-01']);
        $this->assertCount(1, $stations, 'Station not found');

        /** @var Station $station */
        $station = $stations[0];

        /*
         * Success case
         */
        $systemServiceMock = Mockery::mock(System::class);
        /*
         * 'mpc clear'
         * 'mpc load mpc-playlist'
         * 'mpc playlist'
         * 'mpc play 1'
         */
        $systemServiceMock
            ->shouldReceive('call')
            ->times(4);
        /*
         * 'mpc load mpc-playlist'
         * 'mpc playlist'
         * 'mpc play 1'
         */
        $systemServiceMock
            ->shouldReceive('validateOutput')
            ->times(3)
            ->andReturn(true, true, true);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $mpc->play($station);
        # playlist should contain station url
        $this->assertStringEqualsFile(__DIR__ . '/../../var/mpc-playlist.m3u', $station->getUrl());

        /*
         * Error case 1: mpc load mpc-playlist failed
         */
        Mockery::close();
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock->shouldReceive('call')
            ->times(2);
        $systemServiceMock
            ->shouldReceive('validateOutput')
            ->times(1)
            ->andThrows(MpcException::class, 'MockedException');
        try {
            $mpc = new MPC(parent::$kernel, $systemServiceMock);
            $mpc->play($station);
            $this->fail('Exception not thrown');
        } catch (MpcException $e) {
            $this->assertStringContainsString('MockedException', $e->getMessage());
        }

        /*
         * Error case 2: Can not reload mpc-playlist
         */
        Mockery::close();
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock->shouldReceive('call')
            ->times(3);
        $systemServiceMock
            ->shouldReceive('validateOutput')
            ->times(1)
            ->andReturn(true);
        $systemServiceMock
            ->shouldReceive('validateOutput')
            ->times(1)
            ->andThrows(MpcException::class, 'MockedException');
        try {
            $mpc = new MPC(parent::$kernel, $systemServiceMock);
            $mpc->play($station);
            $this->fail('Exception not thrown');
        } catch (MpcException $e) {
            $this->assertStringContainsString('MockedException', $e->getMessage());
        }

        /*
         * Error case 3: Can not start player
         */
        Mockery::close();
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock->shouldReceive('call');
        $systemServiceMock
            ->shouldReceive('validateOutput')
            ->times(2)
            ->andReturn(true, true);
        $systemServiceMock
            ->shouldReceive('validateOutput')
            ->times(1)
            ->andThrows(MpcException::class, 'MockedException');
        try {
            $mpc = new MPC(parent::$kernel, $systemServiceMock);
            $mpc->play($station);
            $this->fail('Exception not thrown');
        } catch (MpcException $e) {
            $this->assertStringContainsString('MockedException', $e->getMessage());
        }

    }

    /**
     * @throws SystemCallException
     * @throws Throwable
     */
    final function testStop(): void
    {
        /// success case
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(2)
            ->andReturn([], []);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $mpc->stop();

        /// fail case
        Mockery::close();
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(2)
            ->andReturn(
                ['mpc stop has been called'],
                ['mpc current has been called']
            );
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        try {
            $mpc->stop();
            $this->fail('Exception not thrown');
        } catch (MpcException $e) {
            $this->assertStringContainsString('Can not stop player', $e->getMessage());
        }
    }

    /**
     * @throws SystemCallException
     */
    final function testGetCurrent(): void
    {
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['[playing]]']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $result = $mpc->getCurrent();
        $this->assertNotEmpty($result);

        Mockery::close();
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $result = $mpc->getCurrent();
        $this->assertEmpty($result);
    }

    /**
     * @throws SystemCallException
     */
    final function testIsPlaying(): void
    {
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['[playing]']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $this->assertTrue($mpc->isPlaying());

        Mockery::close();
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $this->assertFalse($mpc->isPlaying());
    }

    /**
     * @throws MpcException
     * @throws SystemCallException
     */
    final function testGetVolume(): void
    {
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['volume: 42%']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $this->assertEquals(42, $mpc->getVolume());

        Mockery::close();
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['error string']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        try {
            $mpc->getVolume();
            $this->fail('Exception not thrown');
        } catch (MpcException $e) {
            $this->assertStringContainsString('can not extract volume', $e->getMessage());
        }
    }

    /**
     * @throws MpcException
     * @throws SystemCallException
     */
    final function testSetVolume(): void
    {
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['volume: 99%   repeat: off   random: off   single: off   consume: off']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $result = $mpc->setVolume(99);
        $this->assertInstanceOf(MPC::class, $result);

        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn([]);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        try {
            $mpc->setVolume(99);
            $this->fail('Exception not thrown');
        } catch (MpcException $e) {
            $this->assertStringContainsString('empty result', $e->getMessage());
        } catch (SystemCallException $e) {
            $this->fail($e->getMessage());
        }
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['some different string']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        try {
            $mpc->setVolume(99);
            $this->fail('Exception not thrown');
        } catch (MpcException $e) {
            $this->assertStringContainsString('can not extract volume', $e->getMessage());
        }

        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['volume: 90%   repeat: off   random: off   single: off   consume: off']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        try {
            $mpc->setVolume(99);
            $this->fail('Exception not thrown');
        } catch (MpcException $e) {
            $this->assertStringContainsString('could not set volume', $e->getMessage());
        } catch (SystemCallException $e) {
            $this->fail($e->getMessage());
        }

    }

    /**
     * @throws MpcException
     * @throws SystemCallException
     */
    final function testIsMpdRunning(): void
    {
        # check mpc service
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['mpd is running.']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $this->assertTrue( $mpc->isMpdRunning() );

        Mockery::close();
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['mpd is NOT running.']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $this->assertFalse( $mpc->isMpdRunning() );
    }


    /**
     * @throws SystemCallException
     */
    final function testGetError(): void
    {
        $systemServiceMock = Mockery::mock(System::class);
        $systemServiceMock
            ->shouldReceive('call')
            ->times(1)
            ->andReturn(['ERROR: Failed to decode url']);
        $mpc = new MPC(parent::$kernel, $systemServiceMock);
        $result = $mpc->getError();
        $this->assertEquals('ERROR: Failed to decode url', $result);
    }

}
