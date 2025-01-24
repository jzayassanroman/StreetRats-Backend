<?php

namespace App\Controller;

use App\Dto\CrearUsuarioDto;
use App\Servicios\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/usuarios')]
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/crear', name: 'user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $crearUsuarioDTO = new CrearUsuarioDto($data);
            $user = $this->userService->createUser($crearUsuarioDTO);

            return new JsonResponse([
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'password' => $user->getPassword(),
            ], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}