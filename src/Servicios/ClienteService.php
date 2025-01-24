<?php

namespace App\Servicios;

use App\Dto\CrearCuentaDto;
use App\Entity\Cliente;
use App\Repository\ClienteRepository;
use App\Repository\UserRepository;

class ClienteService
{
    private ClienteRepository $clienteRepository;
    private UserRepository $userRepository;

    public function __construct(ClienteRepository $clienteRepository, UserRepository $userRepository)
    {
        $this->clienteRepository = $clienteRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Obtener todos los clientes
     *
     * @return array
     */
    public function findAll(): array
    {
        return Cliente::all()->toArray();
    }

    /**
     * Crear un cliente
     *
     * @param CrearCuentaDto $data
     * @return Cliente
     */
    public function createCliente(CrearCuentaDto $data): Cliente
    {
        // Traducir id_user a un entity User
        $user = $this->userRepository->find($data->getIdUsuario());

        $clienteData = [
            'nombre' => $data->getNombre(),
            'apellido' => $data->getApellido(),
            'email' => $data->getEmail(),
            'telefono' => $data->getTelefono(),
            'direccion' => $data->getDireccion(),
            'user' => $user,
        ];

        return $this->clienteRepository->create($clienteData);
    }
}
