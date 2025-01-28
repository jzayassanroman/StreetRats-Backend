<?php

namespace App\Repository;

use App\Entity\Colores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Colores>
 */
class ColoresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Colores::class);
    }
    public function findAllColores(): array
    {
        return $this->createQueryBuilder('co')
            ->getQuery()
            ->getArrayResult();
    }

    public function save(Colores $color): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($color);
        $entityManager->flush();
    }

//    /**
//     * @return Colores[] Returns an array of Colores objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Colores
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
