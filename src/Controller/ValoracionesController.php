<?php

namespace App\Controller;

use App\Entity\Cliente;
use App\Entity\Productos;
use App\Entity\User;
use App\Entity\Valoraciones;


use App\Repository\ValoracionesRepository;
use App\Servicios\ValoracionesService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;


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






    #[Route('/nueva', name: 'post_valoracion', methods: ['POST'])]
    public function postValoracion(Request $request, EntityManagerInterface $entityManager, JWTEncoderInterface $jwtEncoder): JsonResponse
    {
        try {
            // Obtener el token JWT de la cabecera
            $token = $request->headers->get('Authorization');
            if (!$token) {
                return new JsonResponse(['error' => 'Token no encontrado'], 401);
            }

            $formatToken = str_replace('Bearer ', '', $token);
            $decodedToken = $jwtEncoder->decode($formatToken);

            if (!$decodedToken || !isset($decodedToken['id'])) {
                return new JsonResponse(['error' => 'Token inválido'], 401);
            }

            $idUsuario = $decodedToken['id'];

            // Buscar el usuario en la base de datos
            $usuario = $entityManager->getRepository(User::class)->find($idUsuario);
            if (!$usuario) {
                return new JsonResponse(['error' => 'Usuario no encontrado'], 400);
            }

            // Buscar el cliente asociado a este usuario (relación en la entidad Cliente)
            $cliente = $entityManager->getRepository(Cliente::class)->findOneBy(['id_user' => $usuario]);
            if (!$cliente) {
                return new JsonResponse(['error' => 'Cliente no encontrado para este usuario'], 400);
            }

            // Validar el producto
            $data = json_decode($request->getContent(), true);
            $idProducto = (int) ($data['id_producto'] ?? 0);
            $producto = $entityManager->getRepository(Productos::class)->find($idProducto);

            if (!$producto) {
                return new JsonResponse([
                    'error' => 'Producto no encontrado',
                    'id_producto' => $idProducto
                ], 400);
            }

            // Crear la valoración
            $valoracion = new Valoraciones();
            $valoracion->setValoracion($data['valoracion']);
            $valoracion->setEstrellas($data['estrellas']);

            try {
                $fecha = new \DateTime($data['fecha']);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Formato de fecha inválido'], 400);
            }

            $valoracion->setFecha($fecha);
            $valoracion->setIdProducto($producto);
            $valoracion->setIdCliente($cliente);

            // Guardar la valoración
            $entityManager->persist($valoracion);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Valoración guardada correctamente'], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
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