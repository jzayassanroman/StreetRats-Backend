<?php

namespace App\Controller;


use App\Dto\CrearCuentaDto;
use App\Entity\Cliente;
use App\Entity\User;
use App\Enum\Rol;
use App\Repository\ClienteRepository;
use App\Repository\UserRepository;
use App\Servicios\ClienteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Response;


#[Route('/clientes')]
class ClienteController extends AbstractController
{
    private ClienteService $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    private function generateVerificationCode(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($characters), 0, 5);
    }

    private function sendVerificationEmail(Cliente $cliente, MailerInterface $mailer, EntityManagerInterface $entityManager): void
    {
        $verificationCode = $this->generateVerificationCode();

        $usuario = $cliente->getIdUser();
        $usuario->setVerificationToken($verificationCode);

        $entityManager->persist($usuario);
        $entityManager->flush();

        $email = (new Email())
            ->from('no-reply@streetrats.com')
            ->to($cliente->getEmail()) // Usar el email de la entidad Cliente
            ->subject('Verificación de Cuenta - StreetRats')
            ->html("
        <html>
        <body>
            <h2>¡Bienvenido a StreetRats!</h2>
            <p>Tu código de verificación es: <strong>$verificationCode</strong></p>
        </body>
        </html>
    ");

        $mailer->send($email);
    }


    #[Route('/todos', name: 'cliente_find_all', methods: ['GET'])]
    public function findAll(): JsonResponse
    {
        try {
            // Obtener todos los clientes
            $clientes = $this->clienteService->findAll();
            return new JsonResponse($clientes, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/crear', name: 'cliente_create', methods: ['POST'])]
    public function create(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $crearCuentaDTO = new CrearCuentaDto($data);
            $cliente = $this->clienteService->createCliente($crearCuentaDTO);

            // Obtener usuario asociado al cliente
            $usuario = $userRepository->findOneBy(['id' => $data['id_usuario']]);
            // $usuario->setUsername($data['username']);
            // $usuario->setPassword($data['password']); // Debería estar hasheado
            // $usuario->setRol(Rol::USER);

            // Generar el token de verificación
            $token = Uuid::uuid4()->toString();
            $usuario->setVerificationToken($token);

            $entityManager->persist($usuario);
            $entityManager->flush();

            // Asignar el usuario al cliente
            $cliente->setIdUser($usuario);
            $cliente->setEmail($data['email']); // Asignar el email al cliente
            $entityManager->persist($cliente);
            $entityManager->flush();

            // Enviar email de verificación
            $this->sendVerificationEmail($cliente, $mailer, $entityManager);

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

    #[Route('/editar/{id}', name: 'cliente_edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        // Obtener los datos del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);

        try {
            $cliente = $this->clienteService->updateCliente($id, $data); // Llamar al servicio para editar el cliente

            return new JsonResponse([
                'id' => $cliente->getId(),
                'nombre' => $cliente->getNombre(),
                'apellido' => $cliente->getApellido(),
                'email' => $cliente->getEmail(),
                'telefono' => $cliente->getTelefono(),
                'direccion' => $cliente->getDireccion(),
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/eliminar/{id}', name: 'cliente_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->clienteService->deleteCliente($id); // Llamar al servicio para eliminar

            return new JsonResponse(['message' => 'Cliente eliminado correctamente.'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/find/{id}', name: 'cliente_find_by_id', methods: ['GET'])]
    public function findById(int $id, ClienteRepository $clienteRepository): JsonResponse
    {
        $cliente = $clienteRepository->find($id);

        if (!$cliente) {
            return $this->json(['error' => 'Cliente no encontrado'], 404);
        }

        return $this->json([
            'id' => $cliente->getId(),
            'nombre' => $cliente->getNombre(),
            'apellido' => $cliente->getApellido(),
            'email' => $cliente->getEmail(),
            'telefono' => $cliente->getTelefono(),
        ]);
    }

    #[Route('/clientes/enviar-codigo/{id}', methods: ['POST'])]
    public function enviarCodigoVerificacion(int $id, ClienteRepository $clienteRepository, MailerInterface $mailer, EntityManagerInterface $entityManager): JsonResponse
    {
        $cliente = $clienteRepository->find($id);

        if (!$cliente) {
            return $this->json(['error' => 'Cliente no encontrado'], 404);
        }

        $this->sendVerificationEmail($cliente, $mailer, $entityManager);

        return $this->json(['message' => 'Código de verificación enviado']);
    }
    // src/Controller/ClienteController.php

// ...

    #[Route('/clientes/verificar-codigo', methods: ['POST'])]
    public function verificarCodigo(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $codigo = $data['codigo'] ?? null;

        if (!$email || !$codigo) {
            return $this->json(['error' => 'Email y código requeridos'], 400);
        }

        // Buscar en la entidad Cliente en lugar de User
        $cliente = $entityManager->getRepository(Cliente::class)->findOneBy(['email' => $email]);

        if (!$cliente) {
            return $this->json(['error' => 'Cliente no encontrado'], 400);
        }

        $usuario = $cliente->getIdUser();

        if (!$usuario) {
            return $this->json(['error' => 'El cliente no tiene un usuario asociado'], 400);
        }

        if ($usuario->getVerificationToken() !== $codigo) {
            return $this->json(['error' => 'Código de verificación incorrecto'], 400);
        }

        // Verificar usuario
        $usuario->setVerified(true);
        $usuario->setVerificationToken(null);

        $entityManager->persist($usuario);
        $entityManager->flush();

        return $this->json(['message' => 'Usuario verificado correctamente']);
    }

// ...



}