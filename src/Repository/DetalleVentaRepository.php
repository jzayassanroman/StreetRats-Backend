<?php

namespace App\Repository;

use App\Entity\DetalleVenta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DetalleVenta>
 */
class DetalleVentaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetalleVenta::class);
    }
    /**
     * Encontrar detalles de venta por pedido
     */
    public function findByPedido(int $pedidoId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.pedido = :pedidoId')
            ->setParameter('pedidoId', $pedidoId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener total de ventas de un producto
     */
    public function getTotalVentasProducto(int $productoId): float
    {
        return $this->createQueryBuilder('d')
            ->select('SUM(d.subtotal)')
            ->andWhere('d.producto = :productoId')
            ->setParameter('productoId', $productoId)
            ->getQuery()
            ->getSingleScalarResult() ?: 0;
    }

//    /**
//     * @return DetalleVenta[] Returns an array of DetalleVenta objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DetalleVenta
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
