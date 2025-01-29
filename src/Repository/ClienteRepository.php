<?php

namespace App\Repository;

use App\Entity\Cliente;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cliente>
 */
class ClienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cliente::class);
    }
    /**
     * Método personalizado para obtener todos los clientes
     *
     * @return Cliente[] Retorna una lista de todos los clientes
     */
    public function findAllClientes(): array
    {
        return $this->createQueryBuilder('c')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Método personalizado para obtener un cliente por ID
     *
     * @param int $id ID del cliente
     * @return Cliente|null Retorna el cliente si existe, de lo contrario retorna NULL
     */
    public function create(array $data): Cliente
    {
        $entityManager = $this->getEntityManager();

        // Verificar que los datos requeridos estén en la solicitud
        if (!isset($data['nombre'], $data['apellido'], $data['email'], $data['telefono'], $data['direccion'], $data['id_usuario'])) {
            throw new \Exception("Faltan campos obligatorios en la solicitud.");
        }

        // Buscar el usuario en la base de datos con el ID proporcionado
        $user = $entityManager->getRepository(User::class)->find($data['id_usuario']);
        if (!$user) {
            throw new \Exception("El usuario con ID {$data['id_usuario']} no existe.");
        }

        // Crear y asignar datos al cliente
        $cliente = new Cliente();
        $cliente->setNombre($data['nombre']);
        $cliente->setApellido($data['apellido']);
        $cliente->setEmail($data['email']);
        $cliente->setTelefono($data['telefono']);
        $cliente->setDireccion($data['direccion']);
        $cliente->setIdUser($user); // Asignar objeto User en lugar de ID

        // Guardar en la base de datos
        $entityManager->persist($cliente);
        $entityManager->flush();

        return $cliente;
    }

    public function updateCliente(int $id, array $data): ?Cliente
    {
        $entityManager = $this->getEntityManager();
        $cliente = $this->find($id); // Buscar cliente por ID
        $entityManager->persist($cliente);
        $entityManager->flush();

        if (!$cliente) {
            throw new \Exception("Cliente no encontrado.");
        }

        // Actualizar los campos del cliente con los datos proporcionados
        if (isset($data['nombre'])) {
            $cliente->setNombre($data['nombre']);
        }
        if (isset($data['apellido'])) {
            $cliente->setApellido($data['apellido']);
        }
        if (isset($data['email'])) {
            $cliente->setEmail($data['email']);
        }
        if (isset($data['telefono'])) {
            $cliente->setTelefono($data['telefono']);
        }
        if (isset($data['direccion'])) {
            $cliente->setDireccion($data['direccion']);
        }

        // Persistir los cambios en la base de datos
        $entityManager->flush();

        return $cliente;
    }

    public function removeCliente(int $id): void
    {
        $entityManager = $this->getEntityManager();
        $cliente = $this->find($id); // Buscar cliente por ID

        if ($cliente) {
            $entityManager->remove($cliente); // Eliminar el cliente
            $entityManager->flush(); // Confirmar cambios en la base de datos
        } else {
            throw new \Exception("Cliente no encontrado.");
        }
    }
}