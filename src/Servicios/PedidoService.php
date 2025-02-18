<?php

namespace App\Servicios;
use App\Entity\Cliente;
use App\Entity\Colores;
use App\Entity\DetalleVenta;
use App\Entity\Inventario;
use App\Entity\Pedido;
use App\Entity\PedidoProducto;
use App\Entity\Productos;
use App\Entity\Tallas;
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

    public function crearPedido(int $clienteId, float $total, string $estado, \DateTimeInterface $fecha, array $productosData): Pedido
    {
        // Buscar el cliente desde la base de datos
        $cliente = $this->entityManager->getRepository(Cliente::class)->find($clienteId);
        if (!$cliente) {
            throw new \InvalidArgumentException("Cliente no encontrado.");
        }

        // Crear el nuevo pedido
        $pedido = new Pedido();
        $pedido->setTotal($total);
        $pedido->setEstado(Estado::from($estado));  // Convertir el estado a un enum
        $pedido->setFecha($fecha);
        $pedido->setIdCliente($cliente);

        // Guardar el pedido en la base de datos
        $this->entityManager->persist($pedido);

        // Insertar los productos en la tabla detalle_venta
        foreach ($productosData as $productoData) {
            if (!isset($productoData['id_producto'])) {
                throw new \InvalidArgumentException("El id_producto es requerido.");
            }

            $producto = $this->entityManager->getRepository(Productos::class)->find($productoData['id_producto']);

            if (!$producto) {
                throw new \InvalidArgumentException("Producto no encontrado.");
            }

            // Crear el registro en la tabla detalle_venta
            $detalleVenta = new DetalleVenta();
            $detalleVenta->setPedido($pedido);
            $detalleVenta->setProducto($producto);
            $detalleVenta->setCantidad($productoData['cantidad']);
            $detalleVenta->setSubtotal($productoData['subtotal']);

            $this->entityManager->persist($detalleVenta);
        }

        // Guardar todo
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
