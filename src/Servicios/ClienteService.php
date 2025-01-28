<?php

namespace App\Servicios;
use App\Entity\Cliente;
use App\Repository\ClienteRepository;

class ClienteService{
    private ClienteRepository $clienteRepository;
    public function __construct(ClienteRepository $clienteRepository){
        $this->clienteRepository = $clienteRepository;
    }
    /**
     * Obtener todos los clientes
     *
     * @return array
     */
    public function findAll(): array
    {
        // Llamamos al método findAllClientes del repositorio
        return $this->clienteRepository->findAllClientes();
    }
    public function createCliente(array $data)
{
    return $this->clienteRepository->create($data);
}
    public function updateCliente(int $id, array $data): ?Cliente
    {
        try {
            return $this->clienteRepository->updateCliente($id, $data); // Llamar al repositorio para actualizar
        } catch (\Exception $e) {
            throw new \Exception("No se pudo actualizar el cliente: " . $e->getMessage());
        }
    }
    public function deleteCliente(int $id): void
    {
        try {
            $this->clienteRepository->removeCliente($id); // Llamar al repositorio para eliminar
        } catch (\Exception $e) {
            throw new \Exception("No se pudo eliminar el cliente: " . $e->getMessage());
        }
    }

}