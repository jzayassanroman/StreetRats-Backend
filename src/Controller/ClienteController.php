<?php

namespace App\Controller;

use App\Dto\CrearCuentaDto;
use App\Entity\Cliente;
use App\Entity\User;
use App\Enum\Rol;
use App\Servicios\ClienteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\Email;

#[Route('/clientes')]
class ClienteController extends AbstractController
{
    private ClienteService $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    private function sendVerificationEmail(Cliente $cliente, MailerInterface $mailer): void
    {
        $token = $cliente->getIdUser()->getVerificationToken();

        $htmlContent = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Verificación de Cuenta - StreetRats</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <style>
                body { background-color: #000; color: #fff; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                .container { position: relative; max-width: 500px; padding: 30px; text-align: center; overflow: hidden; border-radius: 10px; background: rgba(255, 255, 255, 0.1); }
                .logo-bg { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(1.2); opacity: 0.1; z-index: 0; width: 100%; }
                .content { position: relative; z-index: 1; }
                .title { font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; }
                .subtitle { margin-top: 10px; font-size: 14px; color: rgba(255, 255, 255, 0.8); }
                .button { display: inline-block; margin-top: 20px; padding: 12px 25px; background: #fff; color: #000; font-weight: bold; text-decoration: none; border-radius: 5px; }
                .footer { margin-top: 20px; font-size: 12px; color: rgba(255, 255, 255, 0.6); }
            </style>
        </head>
        <body>
            <div class="container">
                <!-- Logo en SVG de fondo -->
                <svg class="logo-bg" viewBox="0 0 200 50" xmlns="http://www.w3.org/2000/svg">
                    <text x="10" y="35" font-size="40" font-weight="bold" fill="white" opacity="0.1">StreetRats</text>
                </svg>

                <div class="content">
                    <h2 class="title">¡Bienvenido a StreetRats!</h2>
                    <p class="subtitle">Verifica tu cuenta haciendo clic en el botón a continuación:</p>
                    <a href="http://example.com/verify?token='.$token.'" class="button">Verificar Cuenta</a>
                    <p class="footer">Si no solicitaste este registro, ignora este mensaje.</p>
                </div>
            </div>
        </body>
        </html>
    ';

        $email = (new Email())
            ->from('noreply@streetrats.com')
            ->to($cliente->getEmail())
            ->subject('Verificación de Cuenta - StreetRats')
            ->html($htmlContent);

        $mailer->send($email);
    }



    #[Route('/crear', name: 'cliente_create', methods: ['POST'])]
    public function create(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Faltan datos requeridos'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $crearCuentaDTO = new CrearCuentaDto($data);
            $cliente = $this->clienteService->createCliente($crearCuentaDTO);

            // Obtener usuario asociado al cliente
            $usuario = new User();
            $usuario->setUsername($data['username']);
            $usuario->setPassword($data['password']); // Debería estar hasheado
            $usuario->setRol(Rol::USER);

            // Generar el token de verificación
            $token = Uuid::uuid4()->toString();
            $usuario->setVerificationToken($token);

            $entityManager->persist($usuario);
            $entityManager->flush();

            // Asignar el usuario al cliente
            $cliente->setIdUser($usuario);
            $entityManager->persist($cliente);
            $entityManager->flush();

            // Enviar email de verificación
            $this->sendVerificationEmail($cliente, $mailer);

            return new JsonResponse([
                'id' => $cliente->getId(),
                'nombre' => $cliente->getNombre(),
                'apellido' => $cliente->getApellido(),
                'email' => $cliente->getEmail(),
                'telefono' => $cliente->getTelefono(),
                'direccion' => $cliente->getDireccion(),
                'mensaje' => 'Cuenta creada correctamente, revisa tu email para verificarla.'
            ], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}