<?php

namespace App\Servicios;

use App\Dto\CrearUsuarioDto;
use App\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(CrearUsuarioDto $data)
    {
        return $this->userRepository->create($data);
    }
}