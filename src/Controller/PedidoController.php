<?php

namespace App\Controller;

use App\Entity\Pedido;
use App\Enum\Estado;
use App\Repository\ClienteRepository;
use App\Repository\PedidoRepository;
use App\Repository\ProductosRepository;
use App\Servicios\PedidoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/pedidos')]
class PedidoController extends AbstractController
{
    private PedidoService $pedidoService;
    private PedidoRepository $pedidoRepository;
    private ProductosRepository $productosRepository;
    private ClienteRepository $clienteRepository;

    public function __construct(PedidoService $pedidoService, PedidoRepository $pedidoRepository,
                                ProductosRepository $productosRepository,
                                ClienteRepository $clienteRepository)
    {
        $this->pedidoService = $pedidoService;
        $this->pedidoRepository = $pedidoRepository;
        $this->productosRepository = $productosRepository;
        $this->clienteRepository = $clienteRepository;
    }

    #[Route('/all', name: 'pedido_find_all', methods: ['GET'])]
    public function findAll(PedidoRepository $pedidoRepository): JsonResponse
    {
        $pedidos = $pedidoRepository->findAll();

        if (!$pedidos) {
            return $this->json(['error' => 'No hay pedidos disponibles'], 404);
        }

        $response = [];

        foreach ($pedidos as $pedido) {
            $response[] = [
                'id' => $pedido->getId(),
                'fecha' => $pedido->getFecha()?->format('Y-m-d'),
                'total' => $pedido->getTotal(),
                'estado' => $pedido->getEstado(),
            ];
        }

        return $this->json($response);
    }


    #[Route('/crear', name: 'crear_pedido', methods: ['POST'])]
    public function crearPedido(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['id_cliente']) || empty($data['total']) || empty($data['productos']) || empty($data['fecha'])) {
            return new JsonResponse(['error' => 'Faltan datos necesarios.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $fecha = new \DateTime($data['fecha']);
            $clienteId = $data['id_cliente'];
            $total = $data['total'];
            $productosData = $data['productos'];

            // Si el estado no viene en la petición, se asigna "en curso" por defecto
            $estado = isset($data['estado']) ? strtolower(trim($data['estado'])) : 'en curso';

            // Normaliza el formato del estado recibido, lo pasamos a minúsculas para que coincida con los valores posibles
            $estadoMapeado = match ($estado) {
                'entregado' => Estado::ENTREGADO->value,  // Pasamos el valor como string
                'cancelado' => Estado::CANCELADO->value,  // Pasamos el valor como string
                'en_curso' => Estado::EN_CURSO->value,  // Pasamos el valor como string
                default => throw new \InvalidArgumentException("Estado no válido: $estado")
            };

            // Pasamos el valor del estado como un string en lugar de como un enum
            $pedido = $this->pedidoService->crearPedido($clienteId, $total, $estadoMapeado, $fecha, $productosData);

            return new JsonResponse([
                'id' => $pedido->getId(),
                'total' => $pedido->getTotal(),
                'estado' => $pedido->getEstado()->value,
                'fecha' => $pedido->getFecha()->format('Y-m-d'),
            ], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}/estado', name: 'actualizar_estado_pedido', methods: ['PUT'])]
    public function actualizarEstado(Request $request, $id, EntityManagerInterface $em): JsonResponse
    {
        // Buscar el pedido por ID
        $pedido = $em->getRepository(Pedido::class)->find($id);

        if (!$pedido) {
            return new JsonResponse(['error' => 'Pedido no encontrado'], 404);
        }

        // Obtener el nuevo estado desde el cuerpo de la solicitud (JSON)
        $data = json_decode($request->getContent(), true);  // Decodificar el contenido JSON
        $nuevoEstado = $data['estado'] ?? null;  // Acceder al campo 'estado'

        if (!$nuevoEstado) {
            return new JsonResponse(['error' => 'Estado no proporcionado'], 400);
        }

        // Verificar que el estado es válido
        try {
            // Convertir el string en el valor del Enum
            $estadoEnum = Estado::from($nuevoEstado);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Estado inválido'], 400);
        }

        // Actualizar el estado del pedido con el valor del enum
        $pedido->setEstado($estadoEnum);

        // Guardar los cambios
        $em->flush();

        return new JsonResponse(['message' => 'Estado actualizado con éxito']);
    }





    #[Route('/editar/{id}', name: 'editar_pedido', methods: ['PUT'])]
    public function editarPedido(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['productoId']) || empty($data['clienteId']) || empty($data['total']) || empty($data['estado']) || empty($data['fecha'])) {
            return new JsonResponse(['error' => 'Faltan datos necesarios.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $pedido = $this->pedidoRepository->find($id);
        if (!$pedido) {
            return new JsonResponse(['error' => 'Pedido no encontrado.'], JsonResponse::HTTP_NOT_FOUND);
        }

        try {
            $producto = $this->productosRepository->find($data['productoId']);
            $cliente = $this->clienteRepository->find($data['clienteId']);

            if (!$producto || !$cliente) {
                return new JsonResponse(['error' => 'Producto o Cliente no encontrado.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $pedido->setIdProducto($producto)
                ->setIdCliente($cliente)
                ->setTotal($data['total'])
                ->setEstado(Estado::from($data['estado']))
                ->setFecha(new \DateTime($data['fecha']));

            $this->pedidoRepository->save($pedido, true);

            return new JsonResponse([
                'id' => $pedido->getId(),
                'total' => $pedido->getTotal(),
                'estado' => $pedido->getEstado()->value,
                'fecha' => $pedido->getFecha()->format('Y-m-d'),
                'producto_id' => $pedido->getIdProducto()->getId(),
                'cliente_id' => $pedido->getIdCliente()->getId(),
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
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

    #[Route('/find/{id}', name: 'pedido_find_by_id', methods: ['GET'])]
    public function findById(int $id, PedidoRepository $pedidoRepository): JsonResponse
    {
        $pedido = $pedidoRepository->find($id);

        if (!$pedido) {
            return $this->json(['error' => 'Pedido no encontrado'], 404);
        }

        return $this->json([
            'id' => $pedido->getId(),
            'fecha' => $pedido->getFecha()?->format('Y-m-d'),
            'total' => $pedido->getTotal(),
            'estado' => $pedido->getEstado(),
        ]);
    }

    #[Route('/cliente/{id_cliente}', name: 'pedidos_por_cliente', methods: ['GET'])]
    public function getPedidosPorCliente(int $id_cliente, PedidoRepository $pedidoRepository): JsonResponse
    {
        $pedidos = $pedidoRepository->findBy(['id_cliente' => $id_cliente]);


        if (!$pedidos) {
            return $this->json(['error' => 'No hay pedidos para este cliente'], 404);
        }

        $result = [];
        foreach ($pedidos as $pedido) {
            $result[] = [
                'id' => $pedido->getId(),
                'fecha' => $pedido->getFecha()->format('Y-m-d'),
                'total' => $pedido->getTotal(),
                'estado' => $pedido->getEstado(),
            ];
        }

        return $this->json($result);
    }

}