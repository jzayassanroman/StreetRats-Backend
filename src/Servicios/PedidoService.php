<?php

namespace App\Servicios;
use App\Entity\Cliente;
use App\Entity\Pedido;
use App\Entity\Productos;
use App\Enum\Estado;
use App\Repository\PedidoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PedidoService
{
    private PedidoRepository $pedidoRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, PedidoRepository $pedidoRepository)
    {
        $this->entityManager = $entityManager;
        $this->pedidoRepository = $pedidoRepository;
    }

    public function crearPedido(int $productoId, int $clienteId, float $total, string $estado, \DateTimeInterface $fecha): Pedido
    {
        // Buscar el producto y el cliente desde la base de datos
        $producto = $this->entityManager->getRepository(Productos::class)->find($productoId);
        $cliente = $this->entityManager->getRepository(Cliente::class)->find($clienteId);

        if (!$producto || !$cliente) {
            throw new \InvalidArgumentException("Producto o cliente no encontrados.");
        }

        // Crear el nuevo pedido
        $pedido = new Pedido();
        $pedido->setTotal($total);
        $pedido->setEstado(Estado::from($estado));  // Convertir el estado a un enum
        $pedido->setFecha($fecha);
        $pedido->setIdProducto($producto);
        $pedido->setIdCliente($cliente);

        // Guardar el pedido en la base de datos
        $this->entityManager->persist($pedido);
        $this->entityManager->flush();

        return $pedido;
    }
    public function editarPedido(int $id, array $data): Pedido
    {
        $pedido = $this->pedidoRepository->find($id);

        if (!$pedido) {
            throw new \Exception('El pedido no existe.');
        }

        return $this->pedidoRepository->editarPedido($pedido, $data);
    }

    public function eliminarPedido(int $id): void
    {
        $pedido = $this->pedidoRepository->find($id);

        if (!$pedido) {
            throw new NotFoundHttpException('Pedido no encontrado.');
        }

        $this->pedidoRepository->delete($pedido);
    }

}