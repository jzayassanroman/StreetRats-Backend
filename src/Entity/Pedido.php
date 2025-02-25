<?php

namespace App\Entity;

use App\Enum\Estado;
use App\Repository\PedidoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PedidoRepository::class)]
#[ORM\Table(name: 'pedido',schema: 'streetrats')]
class Pedido
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\Column(enumType: Estado::class)]
    private ?Estado $estado = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_cliente',nullable: false)]
    private ?Cliente $id_cliente = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getEstado(): ?Estado
    {
        return $this->estado;
    }

    public function setEstado(Estado $estado): static
    {
        $this->estado = $estado;

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



    public function getIdCliente(): ?Cliente
    {
        return $this->id_cliente;
    }

    public function setIdCliente(?Cliente $id_cliente): static
    {
        $this->id_cliente = $id_cliente;

        return $this;
    }
}
