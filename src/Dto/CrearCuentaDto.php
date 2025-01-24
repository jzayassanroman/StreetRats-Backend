<?php
namespace App\Dto;

class CrearCuentaDto
{
    private string $nombre;
    private string $apellido;
    private string $email;
    private string $telefono;
    private string $direccion;
    private ?int $id_usuario; // Puede ser null si se permite opcionalmente

    public function __construct(array $data)
    {
        $this->nombre = $data['nombre'] ?? '';
        $this->apellido = $data['apellido'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->telefono = $data['telefono'] ?? '';
        $this->direccion = $data['direccion'] ?? '';
        $this->id_usuario = $data['id_usuario'] ?? null; // Asegúrate de manejar este campo correctamente
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTelefono(): string
    {
        return $this->telefono;
    }

    public function getDireccion(): string
    {
        return $this->direccion;
    }

    public function getIdUsuario(): ?int
    {
        return $this->id_usuario;
    }
}