<?php

namespace App\Controller;

use App\Servicios\PedidoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/pedidos')]

class PedidoController extends AbstractController
{
    private PedidoService $pedidoService;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    #[Route('/crear', name: 'crear_pedido', methods: ['POST'])]
    public function crearPedido(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validar que los datos necesarios estén presentes
        if (empty($data['productoId']) || empty($data['clienteId']) || empty($data['total']) || empty($data['estado']) || empty($data['fecha'])) {
            return new JsonResponse(['error' => 'Faltan datos necesarios.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            // Crear el pedido pasando los datos, incluyendo estado y fecha
            $pedido = $this->pedidoService->crearPedido(
                $data['productoId'],
                $data['clienteId'],
                $data['total'],
                $data['estado'],  // Se incluye el estado
                new \DateTime($data['fecha'])  // Se convierte la fecha en un objeto DateTime
            );

            // Retornar los detalles del pedido creado
            return new JsonResponse([
                'id' => $pedido->getId(),
                'total' => $pedido->getTotal(),
                'estado' => $pedido->getEstado()->value,
                'fecha' => $pedido->getFecha()->format('Y-m-d'),
                'producto_id' => $pedido->getIdProducto()->getId(),
                'cliente_id' => $pedido->getIdCliente()->getId(),
            ], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/eliminar/{id}', name: 'eliminar_pedido', methods: ['DELETE'])]
    public function eliminarPedido(int $id): JsonResponse
    {
        try {
            $this->pedidoService->eliminarPedido($id);

            return new JsonResponse(['message' => 'Pedido eliminado correctamente.'], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

}