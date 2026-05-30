<?php

namespace App\Tests\Controller;

use App\Entity\Station;
use App\Exception\MpcException;
use App\Exception\SystemCallException;
use App\Repository\StationRepository;
use App\Service\MPC;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;


class StationControllerTest extends WebTestCase
{

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/stations');
        self::assertResponseStatusCodeSame(200);
    }

    public function testPlayRedirectsToIndex(): void
    {
        $client = static::createClient();

        /** @var StationRepository $repository */
        $repository = static::getContainer()->get(StationRepository::class);
        $stations = $repository->getAllSorted();
        $station = array_shift($stations);

        $client->request(Request::METHOD_GET, '/station/' . $station->getId() . '/play');
        // play() wraps mpc calls in try-catch(Throwable), so always results in a redirect
        self::assertResponseStatusCodeSame(302);
    }

    public function testPlayNotFound(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/station/99999/play');
        self::assertResponseStatusCodeSame(404);
    }

    public function testPlayerStop(): void
    {
        $client = static::createClient();

        $mockMpc = $this->createMock(MPC::class);
        $mockMpc->expects($this->once())->method('stop');
        static::getContainer()->set(MPC::class, $mockMpc);

        $client->request(Request::METHOD_GET, '/station/stop');
        self::assertResponseRedirects('/stations');
    }

    public function testDelete(): void
    {
        $client = static::createClient();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $station = new Station();
        $station->setName('__test_delete__')->setUrl('http://delete.test.local')->setLogoName('none');
        $em->persist($station);
        $em->flush();
        $id = $station->getId();

        $client->request(Request::METHOD_GET, '/station/' . $id . '/delete');
        self::assertResponseRedirects('/stations');

        $em->clear();
        self::assertNull($em->getRepository(Station::class)->find($id));
    }

    public function testDeleteNotFound(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/station/99999/delete');
        self::assertResponseRedirects('/stations');
    }

    public function testNextRedirects(): void
    {
        $client = static::createClient();

        /** @var StationRepository $repository */
        $repository = static::getContainer()->get(StationRepository::class);
        $stations = $repository->getAllSorted();
        $firstStation = $stations[0];

        // Write playlist file so getCurrent() can find the current station
        /** @var KernelInterface $kernel */
        $kernel = static::getContainer()->get('kernel');
        file_put_contents(
            $kernel->getProjectDir() . '/var/mpc-playlist.m3u',
            $firstStation->getUrl()
        );

        $client->request(Request::METHOD_GET, '/station/next');
        // next() wraps mpc->play() in try-catch(Throwable), so always results in a redirect
        self::assertResponseStatusCodeSame(302);
    }

    /**
     *
     * @throws MpcException
     * @throws SystemCallException
     * @throws Exception
     */
    #[Group('needs-container')]
    public function testPlay(): void
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
     * @return void
     * @throws Exception
     */
    #[Depends('testPlay')]
    #[Group('needs-container')]
    public function testNext(): void
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
