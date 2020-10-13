<?php


namespace App\DataFixtures;


use App\Entity\Station;
use App\Service\FileService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StationFixtures extends Fixture
{

    /**
     * @var FileService
     */
    private FileService $fileService;

    /**
     * StationFixtures constructor.
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        $station = new Station();
        $station
            ->setName('My-Test-Station-01')
            ->setUrl('https://stream.radio.com?st=testUpdateStation')
            ->setLogoName(uniqid());
        $manager->persist($station);

        $station = new Station();
        $station
            ->setName('My-Test-Station-02')
            ->setUrl('https://stream.radio.com?st=testDeleteStation')
            ->setLogoName(uniqid());
        $manager->persist($station);

        $station = new Station();
        $station
            ->setName('N-Joy')
            ->setUrl('http://www.ndr.de/resources/metadaten/audio/m3u/n-joy.m3u');
        $this->fileService->addLogoToStation(
            new UploadedFile(__DIR__ . '/n-joy.png', 'n-joy.png'),
            $station,
            true
        );
        $manager->persist($station);

        $station = new Station();
        $station
            ->setName('Antenne1')
            ->setUrl('http://stream.antenne1.de/a1stg/livestream2.mp3');
        $this->fileService->addLogoToStation(
            new UploadedFile(__DIR__ . '/antenne1.png', 'antenne1.png'),
            $station,
            true
        );
        $manager->persist($station);

        $station = new Station();
        $station
            ->setName('DASDING')
            ->setUrl('https://swr-dasding-live.sslcast.addradio.de/swr/dasding/live/mp3/128/stream.mp3');
        $this->fileService->addLogoToStation(
            new UploadedFile(__DIR__ . '/dasding.png', 'dasding.png'),
            $station,
            true
        );
        $manager->persist($station);

        $station = new Station();
        $station
            ->setName('Radio Brocken')
            ->setUrl('https://www.radiobrocken.de/programm/radio-hoeren-die-radio-brocken-webradios-id10679.html');
        $this->fileService->addLogoToStation(
            new UploadedFile(__DIR__ . '/radiobrocken.jpg', 'radiobrocken.jpg'),
            $station,
            true
        );
        $manager->persist($station);

        $station = new Station();
        $station
            ->setName('Sunshine Live')
            ->setUrl('http://stream.sunshine-live.de/2000er/mp3-192/stream.sunshine-live.de/');
        $this->fileService->addLogoToStation(
            new UploadedFile(__DIR__ . '/sunshine-live.png', 'sunshine-live.png'),
            $station,
            true
        );
        $manager->persist($station);

        $manager->flush();
    }
}
