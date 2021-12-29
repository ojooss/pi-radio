<?php

namespace App\Tests\Controller;

use App\Entity\Station;
use App\Exception\MpcException;
use App\Exception\SystemCallException;
use App\Repository\StationRepository;
use App\Service\MPC;
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

    /**
     * @throws MpcException
     * @throws SystemCallException
     */
    public function testPlay()
    {
        $client = static::createClient();

        /** @var MPC $mpc */
        $mpc = self::getContainer()->get(MPC::class);
        if (!$mpc->isMpdRunning()) {
            $mpc->startMpd();
        }

        /** @var StationRepository $repository */
        $repository = self::getContainer()->get(StationRepository::class);
        $stations = $repository->getAllSorted();
        $station = array_shift($stations);

        $client->request(Request::METHOD_GET, '/station/' . $station->getId() . '/play');
        self::assertResponseStatusCodeSame(302);

        $currentStation = $repository->getCurrent();
        self::assertInstanceOf(Station::class, $currentStation);
        self::assertEquals($station->getId(), $currentStation->getId());
    }

    /**
     * @depends testPlay
     *
     * @return void
     */
    public function testNext()
    {
        $client = static::createClient();

        /** @var StationRepository $repository */
        $repository = self::getContainer()->get(StationRepository::class);

        $station = $repository->getCurrent();
        self::assertInstanceOf(Station::class, $station);

        $stations = $repository->getAllSorted();
        array_shift($stations); // should be playing
        $station = array_shift($stations); // should be next
        $client->request(Request::METHOD_GET, '/station/next');
        self::assertResponseStatusCodeSame(302);
        $currentStation = $repository->getCurrent();
        self::assertEquals($station->getId(), $currentStation->getId());

    }

}
