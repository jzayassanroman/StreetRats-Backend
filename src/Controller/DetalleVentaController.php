<?php

namespace App\Controller;
use App\Entity\DetalleVenta;
use App\Entity\Pedido;
use App\Entity\Productos;
use App\Repository\DetalleVentaRepository;
use App\Servicios\DetalleVentaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/ventas')]
class DetalleVentaController extends AbstractController
{
    private $detalleVentaService;
    public function __construct(DetalleVentaService $detalleVentaService)
    {
        $this->detalleVentaService = $detalleVentaService;
    }

    #[Route('/crear', name: 'detalle_venta_crear', methods: ['POST'])]
    public function crearDetalleVenta(Request $request): Response
    {
        // Obtener los datos del JSON
        $data = json_decode($request->getContent(), true);

        if (!isset($data['pedidoId'], $data['productoId'], $data['cantidad'])) {
            return new Response('Faltan parámetros en la solicitud', Response::HTTP_BAD_REQUEST);
        }

        // Crear el detalle de venta a través del servicio
        try {
            $detalleVenta = $this->detalleVentaService->crearDetalleVenta(
                $data['pedidoId'],
                $data['productoId'],
                $data['cantidad']
            );
            return $this->json([
                'id' => $detalleVenta->getId(),
                'cantidad' => $detalleVenta->getCantidad(),
                'subtotal' => $detalleVenta->getSubtotal(),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/editar/{id}', name: 'detalle_venta_editar', methods: ['PUT'])]
    public function editarDetalleVenta(int $id, Request $request): Response
    {
        // Obtener los datos del JSON
        $data = json_decode($request->getContent(), true);

        if (!isset($data['pedidoId'], $data['productoId'], $data['cantidad'])) {
            return new Response('Faltan parámetros en la solicitud', Response::HTTP_BAD_REQUEST);
        }

        // Editar el detalle de venta a través del servicio
        try {
            $detalleVenta = $this->detalleVentaService->editarDetalleVenta(
                $id,
                $data['pedidoId'],
                $data['productoId'],
                $data['cantidad']
            );
            return $this->json([
                'id' => $detalleVenta->getId(),
                'cantidad' => $detalleVenta->getCantidad(),
                'subtotal' => $detalleVenta->getSubtotal(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/eliminar/{id}', name: 'eliminar_detalle_venta', methods: ['DELETE'])]
    public function eliminarDetalleVenta(int $id): Response
    {
        // Eliminar el detalle de venta
        try {
            $this->detalleVentaService->eliminarDetalleVenta($id);
            return new Response('Detalle de venta eliminado correctamente.', Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/find/{id}', name: 'detalle_venta_find_by_id', methods: ['GET'])]
    public function findById(int $id, DetalleVentaRepository $detalleVentaRepository): JsonResponse
    {
        $detalleVenta = $detalleVentaRepository->find($id);

        if (!$detalleVenta) {
            return $this->json(['error' => 'DetalleVenta no encontrado'], 404);
        }

        return $this->json([
            'id' => $detalleVenta->getId(),
            'cantidad' => $detalleVenta->getCantidad(),
            'subtotal' => $detalleVenta->getSubtotal(),
            'id_pedido' => $detalleVenta->getPedido()?->getId(),
            'id_producto' => $detalleVenta->getProducto()?->getId(),
        ]);
    }

}