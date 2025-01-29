<?php

namespace App\Controller;
use App\Repository\UserRepository;

use App\Dto\CrearUsuarioDto;
use App\Entity\User;
use App\Enum\Rol;
use App\Servicios\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/usuarios')]
class UserController extends AbstractController
{
    private $userRepository;
    private UserService $userService;

    public function __construct(UserService $userService, UserRepository $userRepository)
    {
        $this->userService = $userService;
        $this->userRepository = $userRepository;
    }

//    #[Route('/crear', name: 'user_create', methods: ['POST'])]
//    public function create(Request $request): JsonResponse
//    {
//        $data = json_decode($request->getContent(), true);
//
//        try {
//            $crearUsuarioDTO = new CrearUsuarioDto($data);
//            $user = $this->userService->createUser($crearUsuarioDTO);
//
//            return new JsonResponse([
//                'id' => $user->getId(),
//                'username' => $user->getUsername(),
//                'password' => $user->getPassword(),
//            ], JsonResponse::HTTP_CREATED);
//
//        } catch (\Exception $e) {
//            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
//        }
//    }

    #[Route('/api/registro', name: 'user_registro', methods: ['POST'])]
    public function registro(Request $request, UserPasswordHasherInterface $userPasswordHasher,
                             EntityManagerInterface $entityManager): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

//        if (!isset($body['username']) || !isset($body['password'])) {
//            return new JsonResponse(['error' => 'Faltan datos requeridos'], JsonResponse::HTTP_BAD_REQUEST);
//        }

        $nuevo_usuario = new User();
        $nuevo_usuario->setUsername($body['username']);
        $nuevo_usuario->setPassword($userPasswordHasher->hashPassword($nuevo_usuario, $body['password']));
        $nuevo_usuario->setRol(Rol::USER);

        $entityManager->persist($nuevo_usuario);
        $entityManager->flush();

        return new JsonResponse([
            'mensaje' => 'Usuario creado correctamente',
            'id' => $nuevo_usuario->getId()
        ], JsonResponse::HTTP_CREATED);

    }
}