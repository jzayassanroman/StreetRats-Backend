<?php

namespace App\Entity;

use App\Repository\ValoracionesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValoracionesRepository::class)]
#[ORM\Table(name: 'valoraciones',schema: 'streetrats')]
class Valoraciones
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $opinion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_producto',nullable: false)]
    private ?productos $id_producto = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_cliente',nullable: false)]
    private ?cliente $id_cliente = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpinion(): ?string
    {
        return $this->opinion;
    }

    public function setOpinion(string $opinion): static
    {
        $this->opinion = $opinion;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;

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

    public function getIdCliente(): ?cliente
    {
        return $this->id_cliente;
    }

    public function setIdCliente(?cliente $id_cliente): static
    {
        $this->id_cliente = $id_cliente;

        return $this;
    }
}
