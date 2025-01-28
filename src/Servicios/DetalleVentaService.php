<?php

namespace App\Servicios;
use App\Entity\DetalleVenta;
use App\Entity\Pedido;
use App\Entity\Productos;
use App\Repository\DetalleVentaRepository;
use App\Repository\PedidoRepository;
use App\Repository\ProductosRepository;
use Doctrine\ORM\EntityManagerInterface;

class DetalleVentaService
{
    private PedidoRepository $pedidoRepository;
    private ProductosRepository $productoRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PedidoRepository $pedidoRepository,
        ProductosRepository $productoRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->pedidoRepository = $pedidoRepository;
        $this->productoRepository = $productoRepository;
        $this->entityManager = $entityManager;
    }

    public function crearDetalleVenta(int $pedidoId, int $productoId, int $cantidad): DetalleVenta
    {
        // Buscar pedido y producto
        $pedido = $this->pedidoRepository->find($pedidoId);
        $producto = $this->productoRepository->find($productoId);

        if (!$pedido || !$producto) {
            throw new \Exception('Pedido o Producto no encontrado.');
        }

        // Crear el DetalleVenta
        $detalleVenta = new DetalleVenta();
        $detalleVenta->setPedido($pedido);
        $detalleVenta->setProducto($producto);
        $detalleVenta->setCantidad($cantidad);

        // No necesitas llamar a `setSubtotal()`, ya que se calcula automáticamente
        $this->entityManager->persist($detalleVenta);
        $this->entityManager->flush();

        return $detalleVenta;
    }
    public function editarDetalleVenta(int $id, int $pedidoId, int $productoId, int $cantidad): DetalleVenta
    {
        // Buscar detalle de venta existente
        $detalleVenta = $this->entityManager->getRepository(DetalleVenta::class)->find($id);

        if (!$detalleVenta) {
            throw new \Exception('Detalle de venta no encontrado.');
        }

        // Buscar pedido y producto
        $pedido = $this->pedidoRepository->find($pedidoId);
        $producto = $this->productoRepository->find($productoId);

        if (!$pedido || !$producto) {
            throw new \Exception('Pedido o Producto no encontrado.');
        }

        // Actualizar los valores
        $detalleVenta->setPedido($pedido);
        $detalleVenta->setProducto($producto);
        $detalleVenta->setCantidad($cantidad);

        // El subtotal se calcula automáticamente cuando se asigna la cantidad o el producto
        $this->entityManager->persist($detalleVenta);
        $this->entityManager->flush();

        return $detalleVenta;
    }
    public function eliminarDetalleVenta(int $id): void
    {
        // Buscar el detalle de venta a eliminar
        $detalleVenta = $this->entityManager->getRepository(DetalleVenta::class)->find($id);

        if (!$detalleVenta) {
            throw new \Exception('Detalle de venta no encontrado.');
        }

        // Eliminar el detalle de venta
        $this->entityManager->remove($detalleVenta);
        $this->entityManager->flush();
    }
}