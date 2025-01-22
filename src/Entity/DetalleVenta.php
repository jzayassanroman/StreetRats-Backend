<?php

namespace App\Entity;

use App\Repository\DetalleVentaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetalleVentaRepository::class)]
#[ORM\Table(name: 'detalle_venta',schema: 'streetrats')]
class DetalleVenta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column]
    private ?float $subtotal = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_pedido',nullable: false)]
    private ?pedido $id_pedido = null;

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

    public function getSubtotal(): ?float
    {
        return $this->subtotal;
    }

    public function setSubtotal(float $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getIdPedido(): ?pedido
    {
        return $this->id_pedido;
    }

    public function setIdPedido(?pedido $id_pedido): static
    {
        $this->id_pedido = $id_pedido;

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
