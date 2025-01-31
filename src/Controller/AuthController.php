<?php

namespace App\Controller;

class AuthController
{
    #[Route('/verificar/{token}', name: 'verificar_cuenta', methods: ['GET'])]
    public function verificarCuenta(string $token, EntityManagerInterface $entityManager): Response
    {
        $usuario = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$usuario) {
            return new JsonResponse(['error' => 'Token inválido'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $usuario->setVerified(true);
        $usuario->setVerificationToken(null);
        $entityManager->flush();

        return new JsonResponse(['mensaje' => 'Cuenta verificada correctamente'], JsonResponse::HTTP_OK);
    }

}