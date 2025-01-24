<?php

namespace App\Repository;

use App\Entity\User;
use App\Dto\CrearUsuarioDto;
use App\Enum\Rol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function create(CrearUsuarioDto $data): User
    {
        $entityManager = $this->getEntityManager();

        $user = new User();
        $user->setUsername($data->getUsername());
        $user->setPassword($data->getPassword()); // Make sure to hash the password
        $user->setRol(Rol::USER); // Set default role to USER

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}