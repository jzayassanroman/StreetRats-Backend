<?php

namespace App\Servicios;
use App\Repository\TallasRepository;
use App\Entity\Tallas;
class TallasService
{
    private TallasRepository $tallasRepository;

    public function __construct(TallasRepository $tallasRepository)
    {
        $this->tallasRepository = $tallasRepository;
    }
    /**
     * Obtener todas las tallas
     *
     * @return array
     */
    public function findAll(): array
    {
        return $this->tallasRepository->findAllTallas();
    }

    /**
     * Crear una nueva talla
     */
    public function createTalla(string $descripcion)
    {
        // Llamamos al repositorio para crear una talla
        return $this->tallasRepository->create($descripcion);
    }


}