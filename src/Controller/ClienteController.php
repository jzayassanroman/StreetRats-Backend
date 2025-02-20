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

    #[Route('/editar', name: 'cliente_edit', methods: ['PUT'])]
    public function edit(Request $request, ClienteRepository $clienteRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no autenticado'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $cliente = $clienteRepository->findOneBy(['id_user' => $user->getId()]);
        if (!$cliente) {
            return new JsonResponse(['error' => 'Cliente no encontrado'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            // Actualizar datos del cliente
            $cliente->setNombre($data['nombre'] ?? $cliente->getNombre());
            $cliente->setApellido($data['apellido'] ?? $cliente->getApellido());
            $cliente->setEmail($data['email'] ?? $cliente->getEmail());
            $cliente->setTelefono($data['telefono'] ?? $cliente->getTelefono());
            $cliente->setDireccion($data['direccion'] ?? $cliente->getDireccion());

            // Actualizar el username del usuario asociado
            if (!empty($data['username'])) {
                $user->setUsername($data['username']);
                $entityManager->persist($user);
            }

            $entityManager->flush(); // Guardar cambios

            return new JsonResponse([
                'message' => 'Cliente y username actualizados correctamente',
                'id' => $cliente->getId()
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al actualizar el cliente'], JsonResponse::HTTP_BAD_REQUEST);
        }
    }


    #[Route('/usuario', name: 'cliente_por_usuario', methods: ['GET'])]
    public function getClienteByUser(ClienteRepository $clienteRepository): JsonResponse
    {
        $user = $this->getUser(); // Obtener el usuario desde el token

        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no autenticado'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Obtener el cliente asociado al usuario autenticado
        $cliente = $clienteRepository->findOneBy(['id_user' => $user->getId()]);

        if (!$cliente) {
            return new JsonResponse(['error' => 'Cliente no encontrado'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Retornar los datos del cliente en formato JSON incluyendo el username
        return new JsonResponse([
            'id' => $cliente->getId(),
            'nombre' => $cliente->getNombre(),
            'apellido' => $cliente->getApellido(),
            'email' => $cliente->getEmail(),
            'telefono' => $cliente->getTelefono(),
            'direccion' => $cliente->getDireccion(),
            'username' => $user->getUsername()  // Asegúrate de incluir el username del usuario
        ], JsonResponse::HTTP_OK);
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

        // Agregar registros de depuración
        error_log('Código enviado: ' . $codigo);
        error_log('Código almacenado: ' . $usuario->getVerificationToken());

        if (trim($usuario->getVerificationToken()) !== trim($codigo)) {
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