<?php

namespace App\Repository;

use App\Entity\Cliente;
use App\Entity\Pedido;
use App\Entity\Productos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pedido>
 */
class PedidoRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pedido::class);
    }
    public function save(Pedido $pedido): void
    {
        $this->getEntityManager()->persist($pedido); // Usa getEntityManager()
        $this->getEntityManager()->flush();
    }
    public function editarPedido(Pedido $pedido, array $data): Pedido
    {
        if (isset($data['total'])) {
            $pedido->setTotal($data['total']);
        }

        if (isset($data['estado'])) {
            $pedido->setEstado($data['estado']);
        }

        if (isset($data['fecha'])) {
            $pedido->setFecha(new \DateTime($data['fecha']));
        }

        if (isset($data['productoId'])) {
            $producto = $this->getEntityManager()->getRepository(Productos::class)->find($data['productoId']);
            if ($producto) {
                $pedido->setIdProducto($producto);
            } else {
                // Manejar el error si el producto no se encuentra
                throw new \Exception("Producto no encontrado");
            }
        }

        if (isset($data['clienteId'])) {
            $cliente = $this->getEntityManager()->getRepository(Cliente::class)->find($data['clienteId']);
            if ($cliente) {
                $pedido->setIdCliente($cliente);
            } else {
                // Manejar el error si el cliente no se encuentra
                throw new \Exception("Cliente no encontrado");
            }
        }

        $this->getEntityManager()->flush();

        return $pedido;
    }

    public function delete(Pedido $pedido): void
    {
        $this->getEntityManager()->remove($pedido); // Usamos getEntityManager() en lugar de $_em
        $this->getEntityManager()->flush();
    }

//    /**
//     * @return Pedido[] Returns an array of Pedido objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Pedido
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
