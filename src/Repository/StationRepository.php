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
     * StationRepository constructor.
     * @param ManagerRegistry $registry
     * @param MPC $MPC
     */
    public function __construct(ManagerRegistry $registry, private readonly MPC $MPC)
    {
        parent::__construct($registry, Station::class);
    }

    /**
     * @return null|Station
     */
    public function getCurrent(): ?Station
    {
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

    /**
     * @return Station[]
     */
    public function getAllSorted(): array
    {
        return $this->findBy([], ['sequenceNr' => 'ASC', 'name' => 'ASC']);
    }
}
