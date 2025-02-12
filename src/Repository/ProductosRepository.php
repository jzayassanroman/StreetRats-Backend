<?php

namespace App\Repository;

use App\Entity\Productos;
use App\Enum\Sexo;
use App\Enum\Tipo;
use App\Entity\Tallas;
use App\Entity\Colores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<Productos>
 */
class ProductosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Productos::class);
    }
    // Método findAll
    public function findAllProductos(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.talla', 't')
            ->addSelect('t')
            ->leftJoin('p.color', 'c')
            ->addSelect('c')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult(); // <-- Devuelve objetos de la entidad Productos
    }

    public function save(Productos $producto, bool $flush = false): void
    {
        $this->getEntityManager()->persist($producto);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function updateProducto(int $id, array $data): ?Productos
    {
        $producto = $this->find($id);

        if (!$producto) {
            return null;
        }

        if (isset($data['nombre'])) {
            $producto->setNombre($data['nombre']);
        }

        if (isset($data['descripcion'])) {
            $producto->setDescripcion($data['descripcion']);
        }

        if (isset($data['tipo'])) {
            $tipo = Tipo::tryFrom($data['tipo']);
            if ($tipo === null) {
                throw new \InvalidArgumentException("El tipo proporcionado no es válido: {$data['tipo']}");
            }
            $producto->setTipo($tipo);
        }

        if (isset($data['precio'])) {
            $producto->setPrecio($data['precio']);
        }

        if (isset($data['imagen'])) {
            $producto->setImagen($data['imagen']);
        }

        if (isset($data['sexo'])) {
            $sexo = Sexo::tryFrom($data['sexo']);
            if ($sexo === null) {
                throw new \InvalidArgumentException("El sexo proporcionado no es válido: {$data['sexo']}");
            }
            $producto->setSexo($sexo);
        }

        if (isset($data['talla'])) {
            $talla = $this->getEntityManager()->getRepository(Tallas::class)->find($data['talla']);
            if (!$talla) {
                throw new \InvalidArgumentException("No se encontró la talla con ID: {$data['talla']}");
            }
            $producto->setTalla($talla);
        }

        if (isset($data['color'])) {
            $color = $this->getEntityManager()->getRepository(Colores::class)->find($data['color']);
            if (!$color) {
                throw new \InvalidArgumentException("No se encontró el color con ID: {$data['color']}");
            }
            $producto->setColor($color);
        }

        $this->getEntityManager()->flush();

        return $producto;
    }
    public function eliminarProducto(int $id): void
    {
        // Buscar el producto en el repositorio por ID
        $producto = $this->productosRepository->find($id);

        if (!$producto) {
            throw new NotFoundHttpException("Producto no encontrado.");
        }

        // Usar el repositorio para eliminar el producto
        $this->productosRepository->eliminarProducto($producto);
    }
    /**
     * Encuentra un producto por su ID
     *
     * @param int $id
     * @return Productos|null
     */
    public function findById(int $id): ?Productos
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.talla', 't')
            ->addSelect('t')
            ->leftJoin('p.color', 'c')
            ->addSelect('c')
            ->where('p.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByTalla(int $idTalla)
    {
        return $this->createQueryBuilder('p')
            ->where('p.talla = :talla')
            ->setParameter('talla', $idTalla)
            ->getQuery()
            ->getResult();
    }

    public function findByColor(int $idColor)
    {
        return $this->createQueryBuilder('p')
            ->where('p.color = :color')
            ->setParameter('color', $idColor)
            ->getQuery()
            ->getResult();
    }

    public function searchAndFilter(?string $nombre, ?string $tipo, ?string $sexo, ?int $idTalla, ?int $idColor)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.talla', 't')
            ->leftJoin('p.color', 'c');

        if ($nombre) {
            $qb->andWhere('p.nombre LIKE :nombre')
                ->setParameter('nombre', '%' . $nombre . '%');
        }

        if ($tipo) {
            $qb->andWhere('p.tipo = :tipo')
                ->setParameter('tipo', $tipo);
        }

        if ($sexo) {
            $qb->andWhere('p.sexo = :sexo')
                ->setParameter('sexo', $sexo);
        }

        if ($idTalla) {
            dump("Filtrando por talla: " . $idTalla);
            $qb->andWhere('t.id = :idTalla')
                ->setParameter('idTalla', $idTalla);
        }

        if ($idColor) {
            dump("Filtrando por color: " . $idColor);
            $qb->andWhere('c.id = :idColor')
                ->setParameter('idColor', $idColor);
        }

        return $qb->getQuery()->getResult();
    }






//    /**
//     * @return Productos[] Returns an array of Productos objects
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

//    public function findOneBySomeField($value): ?Productos
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
