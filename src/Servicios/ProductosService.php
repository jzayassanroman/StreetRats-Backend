<?php

namespace App\Servicios;

use App\Enum\Tipo;
use App\Enum\Sexo;
use App\Repository\ProductosRepository;
use App\Entity\Productos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Tallas;

class ProductosService
{
    private ProductosRepository $productosRepository;
    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager, ProductosRepository $productosRepository)
    {
        $this->entityManager = $entityManager;
        $this->productosRepository = $productosRepository;
    }
    public function getAllProductos(): array
    {
        return $this->productosRepository->findAllProductos();
    }

    public function createProducto(array $data): Productos
    {
        // Validar campos obligatorios
        if (
            empty($data['nombre']) ||
            empty($data['descripcion']) ||
            empty($data['tipo']) ||
            empty($data['precio']) ||
            empty($data['imagen']) ||
            empty($data['sexo']) ||
            empty($data['talla']) ||
            empty($data['color'])
        ) {
            throw new \InvalidArgumentException("Todos los campos son obligatorios.");
        }

        // Crear nueva instancia de Producto
        $producto = new Productos();
        $producto->setNombre($data['nombre']);
        $producto->setDescripcion($data['descripcion']);

        // Validar y setear el tipo
        try {
            $producto->setTipo(Tipo::from($data['tipo']));
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Tipo de producto inválido.");
        }

        // Validar y setear el precio
        if (!is_numeric($data['precio']) || $data['precio'] <= 0) {
            throw new \InvalidArgumentException("El precio debe ser un número positivo.");
        }
        $producto->setPrecio((float)$data['precio']);

        // Setear imagen
        $producto->setImagen($data['imagen']);

        // Validar y setear el sexo
        try {
            $producto->setSexo(Sexo::from($data['sexo']));
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Sexo inválido. Los valores permitidos son: Hombre, Mujer, Unisex.");
        }

        // Buscar y setear la talla
        $talla = $this->entityManager->getRepository('App\Entity\Tallas')->find($data['talla']);
        if (!$talla) {
            throw new \InvalidArgumentException("Talla no encontrada.");
        }
        $producto->setTalla($talla);

        // Buscar y setear el color
        $color = $this->entityManager->getRepository('App\Entity\Colores')->find($data['color']);
        if (!$color) {
            throw new \InvalidArgumentException("Color no encontrado.");
        }
        $producto->setColor($color);

        // Guardar el producto en el repositorio
        $this->productosRepository->save($producto, true);

        return $producto;
    }


    public function findProductoById(int $id, array $data): Productos
    {
        $producto = $this->productosRepository->find($id);

        if (!$producto) {
            throw new NotFoundHttpException("Producto no encontrado.");
        }

        if (isset($data['nombre'])) {
            $producto->setNombre($data['nombre']);
        }

        if (isset($data['descripcion'])) {
            $producto->setDescripcion($data['descripcion']);
        }

        if (isset($data['tipo'])) {
            try {
                $producto->setTipo(Tipo::from($data['tipo']));
            } catch (\ValueError $e) {
                throw new \InvalidArgumentException("Tipo de producto inválido.");
            }
        }

        if (isset($data['precio'])) {
            $producto->setPrecio($data['precio']);
        }

        if (isset($data['imagen'])) {
            $producto->setImagen($data['imagen']);
        }

        if (isset($data['sexo'])) {
            try {
                $producto->setSexo(Sexo::from($data['sexo']));
            } catch (\ValueError $e) {
                throw new \InvalidArgumentException("Sexo inválido. Los valores permitidos son: Hombre, Mujer, Unisex.");
            }
        }

        if (isset($data['talla'])) {
            $talla = $this->entityManager->getRepository('App\Entity\Tallas')->find($data['talla']);
            if ($talla) {
                $producto->setTalla($talla);
            }
        }

        if (isset($data['color'])) {
            $color = $this->entityManager->getRepository('App\Entity\Colores')->find($data['color']);
            if ($color) {
                $producto->setColor($color);
            }
        }

        $this->entityManager->flush();

        return $producto;
    }

    public function eliminarProducto(int $id): void
    {
        $producto = $this->productosRepository->find($id);

        if (!$producto) {
            throw new NotFoundHttpException("Producto no encontrado.");
        }

        $this->productosRepository->eliminarProducto($producto);
    }
    public function obtenerProductoPorId(int $id): ?array
    {
        $producto = $this->productosRepository->findById($id);

        if (!$producto) {
            return null; // Producto no encontrado
        }

        return [
            'id' => $producto->getId(),
            'nombre' => $producto->getNombre(),
            'descripcion' => $producto->getDescripcion(),
            'tipo' => $producto->getTipo(),
            'precio' => $producto->getPrecio(),
            'imagen' => $producto->getImagen(),
            'sexo' => $producto->getSexo(),
            'talla' => $producto->getTalla()?->getDescripcion(), // Si tienes el método getNombre() en Tallas
            'color' => $producto->getColor()?->getDescripcion()  // Si tienes el método getNombre() en Colores
        ];
    }

}