<?php

namespace App\Tests\Entity;

use App\Entity\Station;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;

class StationTest extends KernelTestCase
{

    protected function tearDown(): void
    {
        parent::tearDown();

        static::bootKernel();

        /** @var EntityManager $em */
        $em = self::$container->get('doctrine')->getManager();
        $query = $em->createQueryBuilder()
            ->delete()
            ->from(Station::class, 's')
            ->where('s.name = :name')
            ->setParameter('name', __CLASS__)
            ->getQuery();
        $query->execute();

    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testLogo(): void
    {
        static::bootKernel();

        /** @var EntityManager $em */
        $em = self::$container->get('doctrine')->getManager();

        $file = new File(__DIR__ . '/../Data/TestLogo.png');
        self::assertTrue($file->isFile());

        $station = new Station();
        $station->setName(__CLASS__);
        $station->setUrl('http://www.dummy.com');
        $station->setImageFile($file);

        $em->persist($station);
        $em->flush();

        /** @var Station $testEntity */
        $testEntity = $em->getRepository(Station::class)->findOneBy(['name' => __CLASS__]);
        self::assertSame($station->getUrl(), $testEntity->getUrl());
        self::assertSame(__DIR__ . '/../Data/TestLogo.png', $testEntity->getImageFile()->getPathname());
        self::assertSame(
            hash_file('sha256', __DIR__ . '/../Data/TestLogo.png'),
            hash_file('sha256', $testEntity->getImageFile()->getPathname())
        );

        $em->remove($station);
        $em->flush();
    }

}
