<?php

namespace App\Repository;

use App\Entity\NbCodeUserEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NbCodeUserEvent>
 *
 * @method NbCodeUserEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method NbCodeUserEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method NbCodeUserEvent[]    findAll()
 * @method NbCodeUserEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NbCodeUserEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NbCodeUserEvent::class);
    }

    public function save(NbCodeUserEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NbCodeUserEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return NbCodeUserEvent[] Returns an array of NbCodeUserEvent objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?NbCodeUserEvent
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
