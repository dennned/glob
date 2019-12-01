<?php

namespace App\Repository;

use App\Entity\VideoYoutube;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use \Doctrine\ORM\NoResultException;
use \Doctrine\ORM\NonUniqueResultException;

/**
 * @method VideoYoutube|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoYoutube|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoYoutube[]    findAll()
 * @method VideoYoutube[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoYoutubeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoYoutube::class);
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function getVideosWithoutTags(int $limit = 10)
    {
        return $this->createQueryBuilder('v')
            ->where("v.tags is null")
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return VideoYoutube|null
     * @throws NonUniqueResultException
     */
    public function findOneVideoNonPosted(): ?VideoYoutube
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.isPosted = :val')
            ->setParameter('val', 0)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
            ;
    }


    // /**
    //  * @return VideoYoutube[] Returns an array of VideoYoutube objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VideoYoutube
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
