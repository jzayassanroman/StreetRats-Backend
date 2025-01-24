<?php

namespace App\Dto;

class CrearUsuarioDto
{
    private string $username;
    private string $password;

    public function __construct(array $data)
    {
        $this->username = $data['username'];
        $this->password = $data['password'];
    }

    public function getUsername(): string
    {
        return $this->username;
    }



    public function getPassword(): string
    {
        return $this->password;
    }
}