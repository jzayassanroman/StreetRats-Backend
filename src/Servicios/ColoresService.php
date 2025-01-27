<?php

namespace App\Servicios;
use App\Repository\ColoresRepository;
use App\Entity\Colores;
class ColoresService
{
    private ColoresRepository $coloresRepository;

    public function __construct(ColoresRepository $coloresRepository)
    {
        $this->coloresRepository = $coloresRepository;
    }

    public function findAll(): array
    {
        return $this->coloresRepository->findAllColores();
    }

    public function create(string $descripcion): Colores
    {
        $color = new Colores();
        $color->setDescripcion($descripcion);

        $this->coloresRepository->save($color);

        return $color;
    }

}