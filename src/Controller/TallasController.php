<?php

namespace App\Controller;
use App\Servicios\TallasService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/tallas')]

class TallasController extends AbstractController
{
    private TallasService $tallasService;

    public function __construct(TallasService $tallasService)
    {
        $this->tallasService = $tallasService;
    }
    #[Route('/all', name: 'talla_all', methods: ['GET'])]
    public function findAll(): JsonResponse
    {
        $tallas = $this->tallasService->findAll();

        return new JsonResponse($tallas, JsonResponse::HTTP_OK);
    }

    #[Route('/crear', name: 'talla_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $talla = $this->tallasService->createTalla($data['descripcion']);

            return new JsonResponse([
                'id' => $talla->getId(),
                'descripcion' => $talla->getDescripcion(),
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

}