<?php

namespace App\Servicios;

use App\Entity\Cliente;
use App\Entity\Productos;
use App\Entity\Valoraciones;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ValoracionesService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function crearValoracion(string $valoracion, int $idProducto, int $idCliente): void
    {
        // Buscar el producto y el cliente por sus IDs
        $producto = $this->entityManager->getRepository(Productos::class)->find($idProducto);
        $cliente = $this->entityManager->getRepository(Cliente::class)->find($idCliente);

        // Comprobar si el producto o cliente no existen
        if (!$producto) {
            throw new NotFoundHttpException('Producto no encontrado.');
        }

        if (!$cliente) {
            throw new NotFoundHttpException('Cliente no encontrado.');
        }

        // Crear la nueva valoración
        $valoraciones = new Valoraciones();
        $valoraciones->setValoracion($valoracion);
        $valoraciones->setFecha(new \DateTime()); // Usar la fecha actual
        $valoraciones->setIdProducto($producto);
        $valoraciones->setIdCliente($cliente);

        // Persistir la valoración en la base de datos
        $this->entityManager->persist($valoraciones);
        $this->entityManager->flush();
    }

}