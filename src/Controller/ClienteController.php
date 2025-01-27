<?php

namespace App\Controller;
use App\Servicios\ClienteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/clientes')]
class ClienteController extends AbstractController
{
    private ClienteService $clienteService;
    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    #[Route('/todos', name: 'cliente_find_all', methods: ['GET'])]
    public function findAll(): JsonResponse
    {
        try {
            // Obtener todos los clientes
            $clientes = $this->clienteService->findAll();

            return new JsonResponse($clientes, JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
    #[Route('/crear', name: 'cliente_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $cliente = $this->clienteService->createCliente($data);

            return new JsonResponse([
                'id' => $cliente->getId(),
                'nombre' => $cliente->getNombre(),
                'apellido' => $cliente->getApellido(),
                'email' => $cliente->getEmail(),
                'telefono' => $cliente->getTelefono(),
                'direccion' => $cliente->getDireccion(),
            ], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
    #[Route('/editar/{id}', name: 'cliente_edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        // Obtener los datos del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);

        try {
            $cliente = $this->clienteService->updateCliente($id, $data); // Llamar al servicio para editar el cliente

            return new JsonResponse([
                'id' => $cliente->getId(),
                'nombre' => $cliente->getNombre(),
                'apellido' => $cliente->getApellido(),
                'email' => $cliente->getEmail(),
                'telefono' => $cliente->getTelefono(),
                'direccion' => $cliente->getDireccion(),
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/eliminar/{id}', name: 'cliente_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->clienteService->deleteCliente($id); // Llamar al servicio para eliminar

            return new JsonResponse(['message' => 'Cliente eliminado correctamente.'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

}