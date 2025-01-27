<?php

namespace App\Entity;

use App\Enum\Sexo;
use App\Enum\Tipo;
use App\Repository\ProductosRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductosRepository::class)]
#[ORM\Table(name: 'producto',schema: 'streetrats')]
class Productos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;

    #[ORM\Column(enumType: Tipo::class)]
    private ?Tipo $tipo = null;

    #[ORM\Column]
    private ?float $precio = null;

    #[ORM\Column(length: 255)]
    private ?string $imagen = null;

    #[ORM\Column(enumType: Sexo::class)]
    private ?Sexo $sexo = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_talla',nullable: false)]
    private ?Tallas $id_talla = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'id_color',nullable: false)]
    private ?Colores $id_color = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getTipo(): ?Tipo
    {
        return $this->tipo;
    }

    public function setTipo(Tipo $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): static
    {
        $this->precio = $precio;

        return $this;
    }

    public function getImagen(): ?string  // Cambia 'getImg' a 'getImagen'
    {
        return $this->imagen;
    }

    public function setImagen(string $imagen): static  // Cambia 'setImg' a 'setImagen'
    {
        $this->imagen = $imagen;

        return $this;
    }

    public function getSexo(): ?Sexo
    {
        return $this->sexo;
    }

    public function setSexo(Sexo $sexo): static
    {
        $this->sexo = $sexo;

        return $this;
    }

    public function getIdTalla(): ?tallas
    {
        return $this->id_talla;
    }

    public function setIdTalla(?tallas $id_talla): static
    {
        $this->id_talla = $id_talla;

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
}
