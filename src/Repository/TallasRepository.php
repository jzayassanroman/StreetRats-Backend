<?php

namespace App\Repository;

use App\Entity\Tallas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tallas>
 */
class TallasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tallas::class);
    }
    /**
     * Método para obtener todas las tallas
     *
     * @return Tallas[]
     */
    public function findAllTallas(): array
    {
        return $this->createQueryBuilder('t')
            ->getQuery()
            ->getArrayResult();
    }
    public function create(string $descripcion): Tallas
    {
        $entityManager = $this->getEntityManager();

        $talla = new Tallas();
        $talla->setDescripcion($descripcion);

        $entityManager->persist($talla);
        $entityManager->flush();

        return $talla;
    }


//    /**
//     * @return Tallas[] Returns an array of Tallas objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Tallas
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
