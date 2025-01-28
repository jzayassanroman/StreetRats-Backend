<?php

namespace App\Controller;

use App\Repository\ColoresRepository;
use App\Servicios\ColoresService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/color')]

class ColoresController extends AbstractController
{
    private ColoresService $coloresService;

    public function __construct(ColoresService $coloresService)
    {
        $this->coloresService = $coloresService;
    }
    #[Route('/all', name: 'find_all_color', methods: ['GET'])]
    public function findAll(): JsonResponse
    {
        $colores = $this->coloresService->findAll();
        return $this->json($colores);
    }

    #[Route('/crear', name: 'create_color', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $descripcion = $data['descripcion'] ?? null;

        if (!$descripcion) {
            return new JsonResponse(['error' => 'La descripción es obligatoria'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $color = $this->coloresService->create($descripcion);

        return new JsonResponse(['id' => $color->getId(), 'descripcion' => $color->getDescripcion()], JsonResponse::HTTP_CREATED);
    }
    #[Route('/find/{id}', name: 'colores_find_by_id', methods: ['GET'])]
    public function findById(int $id, ColoresRepository $coloresRepository): JsonResponse
    {
        $color = $coloresRepository->find($id);

        if (!$color) {
            return $this->json(['error' => 'Color no encontrado'], 404);
        }

        return $this->json([
            'id' => $color->getId(),
            'descripcion' => $color->getDescripcion(),
        ]);
    }

}