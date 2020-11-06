<?php

namespace App\Repository;

use App\Entity\Station;
use App\Service\MPC;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Station|null find($id, $lockMode = null, $lockVersion = null)
 * @method Station|null findOneBy(array $criteria, array $orderBy = null)
 * @method Station[]    findAll()
 * @method Station[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StationRepository extends ServiceEntityRepository
{
    /**
     * @var MPC
     */
    private MPC $MPC;

    /**
     * StationRepository constructor.
     * @param ManagerRegistry $registry
     * @param MPC $MPC
     */
    public function __construct(ManagerRegistry $registry, MPC $MPC)
    {
        parent::__construct($registry, Station::class);
        $this->MPC = $MPC;
    }

    /**
     * @return null|Station
     */
    public function getCurrent() {
        $content = $this->MPC->getPlaylistFileContent();
        $entities = $this->createQueryBuilder('s')
            ->andWhere('s.url = :url')
            ->setParameter('url', $content)
            ->getQuery()
            ->getResult()
            ;
        if (count($entities) === 1) {
            return current($entities);
        } else {
            return null;
        }
    }

    // /**
    //  * @return Pipe[] Returns an array of Pipe objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pipe
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
