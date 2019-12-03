<?php

namespace App\Repository;

use App\Entity\CronLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * @method CronLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method CronLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method CronLog[]    findAll()
 * @method CronLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CronLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CronLog::class);
    }

    /**
     * @return mixed
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countLogs()
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getLatestLogs()
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :val')
            ->setParameter('val', 1)
            ->andWhere("c.nextPage <> ''")
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return CronLog[] Returns an array of CronLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CronLog
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
