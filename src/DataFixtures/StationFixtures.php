<?php


namespace App\DataFixtures;


use App\Entity\Station;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StationFixtures  extends Fixture
{

    public function load(ObjectManager $manager)
    {

        $station = new Station();
        $station
            ->setName('My-Test-Station-01')
            ->setUrl('https://stream.radio.com?st=testUpdateStation');
        $manager->persist($station);

        $station = new Station();
        $station
            ->setName('My-Test-Station-02')
            ->setUrl('https://stream.radio.com?st=testDeleteStation');
        $manager->persist($station);

        $station = new Station();
        $station
            ->setName('My-Test-Station-03')
            ->setUrl('https://stream.radio.com?st=testStationWithLogo')
            ->setImageFile(
                new File( __DIR__ . '/../../tests/Data/TestLogo.png', true )
            );
        $manager->persist($station);

        $faker = Factory::create('de_DE');
        $station = new Station();
        $station
            ->setName($faker->domainName)
            ->setUrl($faker->url);
        $manager->persist($station);

        $manager->flush();

        /*

        App\Entity\Station:
  station_{1..8}:
    name: <name()>
    url: <url()>
  station_9:
    name: My-Test-Station-01
    url: https://stream.radio.com?st=testUpdateStation
  station_10:
    name: My-Test-Station-02
    url: https://stream.radio.com?st=testDeleteStation

# https://github.com/hautelook/AliceBundle
# -> php bin/console hautelook:fixtures:load
        */

    }
}
