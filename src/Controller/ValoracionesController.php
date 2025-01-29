<?php

namespace App\Controller;

use App\Repository\ValoracionesRepository;
use App\Servicios\ValoracionesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/valoraciones')]

class ValoracionesController extends AbstractController
{
    private ValoracionesService $valoracionService;

    public function __construct(ValoracionesService $valoracionService)
    {
        $this->valoracionService = $valoracionService;
    }

    #[Route('/crear', name: 'crear_valoracion', methods: ['POST'])]
    public function crearValoracion(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Validar que los campos necesarios estén presentes
        if (empty($data['valoracion']) || empty($data['id_producto']) || empty($data['id_cliente']) || !isset($data['estrellas'])) {
            return new Response('Faltan campos necesarios.', Response::HTTP_BAD_REQUEST);
        }

        try {
            // Crear la valoración
            $this->valoracionService->crearValoracion($data['valoracion'], (int) $data['estrellas'], $data['id_producto'], $data['id_cliente']);
            return new Response('Valoración creada correctamente.', Response::HTTP_CREATED);
        } catch (\RuntimeException $e) {
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/find/{id}', name: 'valoraciones_find_by_id', methods: ['GET'])]
    public function findById(int $id, ValoracionesRepository $valoracionesRepository): JsonResponse
    {
        $valoracion = $valoracionesRepository->find($id);

        if (!$valoracion) {
            return $this->json(['error' => 'Valoración no encontrada'], 404);
        }

        return $this->json([
            'id' => $valoracion->getId(),
            'valoracion' => $valoracion->getValoracion(),
            'fecha' => $valoracion->getFecha()?->format('Y-m-d'),
            'id_producto' => $valoracion->getIdProducto()?->getId(),
            'id_cliente' => $valoracion->getIdCliente()?->getId(),
        ]);
    }

}