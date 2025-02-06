<?php

namespace App\Controller;

use App\Entity\Cliente;
use App\Entity\Productos;
use App\Entity\Valoraciones;
use App\Repository\ValoracionesRepository;
use App\Servicios\ValoracionesService;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/{id_producto}', name: 'get_valoraciones', methods: ['GET'])]
    public function getValoraciones(int $id_producto, ValoracionesRepository $repository): JsonResponse
    {
        $valoraciones = $repository->findBy(['id_producto' => $id_producto]);
        $data = [];

        foreach ($valoraciones as $val) {
            $data[] = [
                'id' => $val->getId(),
                'valoracion' => $val->getValoracion(),
                'estrellas' => $val->getEstrellas(),
                'fecha' => $val->getFecha()->format('Y-m-d')
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'post_valoracion', methods: ['POST'])]
    public function postValoracion(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $valoracion = new Valoraciones();
        $valoracion->setValoracion($data['valoracion']);
        $valoracion->setEstrellas($data['estrellas']);
        $valoracion->setFecha(new \DateTime($data['fecha']));

        // Aquí debes obtener el producto y cliente reales de la BD
        $producto = $entityManager->getRepository(Productos::class)->find($data['id_producto']);
        $cliente = $entityManager->getRepository(Cliente::class)->find($data['id_cliente']);

        if (!$producto || !$cliente) {
            return new JsonResponse(['error' => 'Producto o Cliente no encontrado'], 400);
        }

        $valoracion->setIdProducto($producto);
        $valoracion->setIdCliente($cliente);

        $entityManager->persist($valoracion);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Valoración guardada correctamente'], 201);
    }


}