<?php

namespace App\Entity;

use App\Repository\PedidoProductoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PedidoProductoRepository::class)]
#[ORM\Table(name: 'pedido_producto',schema: 'streetrats')]
class PedidoProducto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_producto',nullable: false)]
    private ?productos $id_producto = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_pedido',nullable: false)]
    private ?pedido $id_pedido = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_color',nullable: false)]
    private ?colores $id_color = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_tallas',nullable: false)]
    private ?tallas $id_tallas = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdPedido(): ?pedido
    {
        return $this->id_pedido;
    }

    public function setIdPedido(?pedido $id_pedido): static
    {
        $this->id_pedido = $id_pedido;

        return $this;
    }

    public function getIdColor(): ?colores
    {
        return $this->id_color;
    }

    public function setIdColor(?colores $id_color): static
    {
        $this->id_color = $id_color;

        return $this;
    }

    public function getIdTallas(): ?tallas
    {
        return $this->id_tallas;
    }

    public function setIdTallas(?tallas $id_tallas): static
    {
        $this->id_tallas = $id_tallas;

        return $this;
    }
}
