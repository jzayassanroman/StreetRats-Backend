<?php

namespace App\Entity;

use App\Repository\InventarioRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventarioRepository::class)]
#[ORM\Table(name: 'inventario',schema: 'streetrats')]
class Inventario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_producto',nullable: false)]
    private ?productos $id_producto = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getIdProducto(): ?productos
    {
        return $this->id_producto;
    }

    public function setIdProducto(?productos $id_producto): static
    {
        $this->id_producto = $id_producto;

        return $this;
    }
}
