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

    public function crearValoracion(string $valoracion, int $estrellas, int $idProducto, int $idCliente): void
    {
        // Validar que las estrellas sean entre 1 y 5
        if ($estrellas < 1 || $estrellas > 5) {
            throw new \InvalidArgumentException('La valoración debe estar entre 1 y 5 estrellas.');
        }

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

        // Verificar si el cliente ya ha valorado este producto
        $valoracionExistente = $this->entityManager->getRepository(Valoraciones::class)
            ->findOneBy(['id_producto' => $producto, 'id_cliente' => $cliente]);

        if ($valoracionExistente) {
            throw new \RuntimeException('Ya has valorado este producto anteriormente.');
        }

        // Crear la nueva valoración
        $valoraciones = new Valoraciones();
        $valoraciones->setValoracion($valoracion);
        $valoraciones->setEstrellas($estrellas);
        $valoraciones->setFecha(new \DateTime()); // Usar la fecha actual
        $valoraciones->setIdProducto($producto);
        $valoraciones->setIdCliente($cliente);

        // Persistir la valoración en la base de datos
        $this->entityManager->persist($valoraciones);
        $this->entityManager->flush();
    }

}