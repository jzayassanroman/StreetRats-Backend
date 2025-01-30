<?php

namespace App\Repository;

use App\Entity\Cliente;
use App\Entity\User;
use App\Dto\CrearCuentaDto;
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

    public function create(array $data): Cliente
    {
        $entityManager = $this->getEntityManager();

        $cliente = new Cliente();
        $cliente->setNombre($data['nombre']);
        $cliente->setApellido($data['apellido']);
        $cliente->setEmail($data['email']);
        $cliente->setTelefono($data['telefono']);
        $cliente->setDireccion($data['direccion']);
        $cliente->setIdUser($data['user']);

        $entityManager->persist($cliente);
        $entityManager->flush();

        return $cliente;
    }

}
