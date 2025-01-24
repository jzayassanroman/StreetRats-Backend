<?php

namespace App\Controller;

use App\Dto\CrearCuentaDto;
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


    #[Route('/crear', name: 'cliente_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $crearCuentaDTO = new CrearCuentaDto($data);
            $cliente = $this->clienteService->createCliente($crearCuentaDTO);

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

}
