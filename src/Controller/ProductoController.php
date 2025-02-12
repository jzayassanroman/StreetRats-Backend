<?php

namespace App\Controller;
use App\Entity\Productos;
use App\Enum\Tipo;
use App\Repository\ColoresRepository;
use App\Repository\ProductosRepository;
use App\Repository\TallasRepository;
use App\Servicios\ProductosService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\Sexo;


#[Route('/productos')]
class ProductoController extends AbstractController
{
    private ProductosService $productosService;
    private ProductosRepository $productosRepository;

    public function __construct(ProductosService $productosService, ProductosRepository $productosRepository)
    {
        $this->productosService = $productosService;
        $this->productosRepository = $productosRepository;
    }
    #[Route('/all', name: 'productos_all')]
    public function index(): JsonResponse
    {
        $productos = $this->productosService->getAllProductos();

        // Convertir la cadena JSON de imagen en un array real
        $productosArray = array_map(function ($producto) {
            return [
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'descripcion' => $producto->getDescripcion(),
                'tipo' => $producto->getTipo(),
                'precio' => $producto->getPrecio(),
                'imagenes' => json_decode($producto->getImagen(), true), // 🔥 CONVIERTE EL STRING A ARRAY 🔥
                'sexo' => $producto->getSexo(),
                'talla' => $producto->getIdTalla() ? $producto->getIdTalla()->getId() : null,
                'color' => $producto->getIdColor() ? $producto->getIdColor()->getId() : null,
            ];
        }, $productos);

        return new JsonResponse($productosArray);
    }



    #[Route('/crear', name: 'producto_create', methods: ['POST'])]
    public function crearProducto(
        Request $request,
        ProductosRepository $productosRepository,
        TallasRepository $tallasRepository,
        ColoresRepository $coloresRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $nombre = $data['nombre'] ?? null;
        $descripcion = $data['descripcion'] ?? null;
        $tipoStr = $data['tipo'] ?? null;
        $precio = $data['precio'] ?? null;
        $imagen = $data['imagen'] ?? null; // Validación de imagen incluida
        $sexoStr = $data['sexo'] ?? null;
        $Talla = $data['talla'] ?? null;
        $Color = $data['color'] ?? null;

        // Verificar que todos los campos obligatorios están presentes
        if (!$nombre || !$descripcion || !$tipoStr || !$precio || !$imagen || !$sexoStr || !$Talla || !$Color) {
            return new JsonResponse(['error' => 'Faltan campos obligatorios'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $talla = $tallasRepository->find($Talla);
        $color = $coloresRepository->find($Color);

        if (!$talla || !$color) {
            return new JsonResponse(['error' => 'Talla o Color no encontrados'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Convertir el valor de tipo al tipo adecuado
        try {
            $tipo = Tipo::from($tipoStr);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Tipo no válido'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Convertir el valor de sexo al tipo adecuado
        try {
            $sexo = Sexo::from($sexoStr);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Sexo no válido'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Crear el producto
        $producto = new Productos();
        $producto->setNombre($nombre);
        $producto->setDescripcion($descripcion);
        $producto->setTipo($tipo);
        $producto->setPrecio((float)$precio);
        $producto->setImagen($imagen); // Validación aplicada
        $producto->setSexo($sexo);
        $producto->setTalla($talla);
        $producto->setColor($color);

        $productosRepository->save($producto, true);

        return new JsonResponse(['message' => 'Producto creado exitosamente'], JsonResponse::HTTP_CREATED);
    }
    #[Route('/editar/{id}', name: 'productos_edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        // Obtener los datos del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true); // El segundo parámetro 'true' convierte el JSON a un array

        try {
            // Llamar al servicio para editar el producto
            $producto = $this->productosService->findProductoById($id, $data);

            // Retornar la respuesta en formato JSON
            return new JsonResponse([
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'descripcion' => $producto->getDescripcion(),
                'tipo' => $producto->getTipo(),
                'precio' => $producto->getPrecio(),
                'imagen' => $producto->getImagen(),
                'sexo' => $producto->getSexo(),
                'talla' => $producto->getTalla() ? $producto->getTalla()->getId() : null,
                'color' => $producto->getColor() ? $producto->getColor()->getId() : null,
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
    #[Route('/eliminar/{id}', name: 'eliminar_producto', methods: ['DELETE'])]
    public function eliminar(int $id): JsonResponse
    {
        try {
            $this->productosService->eliminarProducto($id);

            return new JsonResponse(['mensaje' => 'Producto eliminado con éxito.'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
    #[Route('/find/{id}', name: 'producto_por_id', methods: ['GET'])]
    public function productoPorId(int $id): JsonResponse
    {
        $producto = $this->productosService->obtenerProductoPorId($id);

        if (!$producto) {
            return new JsonResponse(['error' => 'Producto no encontrado'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($producto, JsonResponse::HTTP_OK);
    }
    #[Route('/tipos', name: 'productos_por_categoria', methods: ['GET'])]
    public function getProductos(ProductosRepository $productoRepository, Request $request): JsonResponse
    {
        $tipo = $request->query->get('tipo');

        if ($tipo !== null) {
            // Convertir a capitalizado para que coincida con los valores del Enum
            $tipo = ucfirst(strtolower($tipo));

            // Obtener los valores válidos del Enum
            $tiposValidos = array_map(fn($tipo) => $tipo->value, Tipo::cases());

            if (!in_array($tipo, $tiposValidos, true)) {
                return new JsonResponse(['error' => 'Tipo de producto no válido'], 400);
            }

            // Convertir el string recibido en un objeto Enum
            $tipoEnum = Tipo::from($tipo);

            // Buscar productos por tipo utilizando el Enum
            $productos = $productoRepository->findBy(['tipo' => $tipoEnum]);
        } else {
            // Si no se proporciona tipo, devolver todos los productos
            $productos = $productoRepository->findAll();
        }

        // Convertir los productos a un array para asegurarnos de que se serializan correctamente
        $productosArray = array_map(function ($producto) {
            return [
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'descripcion' => $producto->getDescripcion(),
                'tipo' => $producto->getTipo() instanceof Tipo ? $producto->getTipo()->value : null, // Verifica si es Enum
                'precio' => $producto->getPrecio(),
                'imagen' => $producto->getImagen(),
                'sexo' => $producto->getSexo() instanceof Sexo ? $producto->getSexo()->value : null,
                'talla' => $producto->getTalla() ? $producto->getTalla()->getId() : null,
                'color' => $producto->getColor() ? $producto->getColor()->getId() : null
            ];
        }, $productos);

        return new JsonResponse($productosArray, 200);
    }






}