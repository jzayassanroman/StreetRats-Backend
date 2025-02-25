<?php

namespace App\Entity;

use App\Repository\DetalleVentaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetalleVentaRepository::class)]
#[ORM\Table(name: 'detalle_venta', schema: 'streetrats')]
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
    #[ORM\JoinColumn(name: 'id_pedido', nullable: false)]
    private ?Pedido $pedido = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_producto', nullable: false, onDelete: 'CASCADE')]
    private ?Productos $producto = null;

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

        // Si el producto está asignado, recalcular el subtotal automáticamente
        if ($this->producto !== null) {
            $this->setSubtotal($this->producto->getPrecio() * $cantidad);
        }

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

    public function getPedido(): ?Pedido
    {
        return $this->pedido;
    }

    public function setPedido(?Pedido $pedido): static
    {
        $this->pedido = $pedido;

        return $this;
    }

    public function getProducto(): ?Productos
    {
        return $this->producto;
    }

    public function setProducto(?Productos $producto): static
    {
        $this->producto = $producto;

        // Si la cantidad está asignada, recalcular el subtotal automáticamente
        if ($this->cantidad !== null) {
            $this->setSubtotal($producto->getPrecio() * $this->cantidad);
        }

        return $this;
    }
}
