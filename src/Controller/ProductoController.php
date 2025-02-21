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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\Sexo;

#[Route('/productos')]
class ProductoController extends AbstractController
{
    private ProductosService $productosService;

    public function __construct(ProductosService $productosService)
    {
        $this->productosService = $productosService;
    }

    #[Route('/buscar', name: 'buscar_productos', methods: ['GET'])]
    public function buscar(Request $request, ProductosRepository $productoRepository): JsonResponse
    {
        $nombre = $request->query->get('nombre');
        $tipo = $request->query->get('tipo');
        $sexo = $request->query->get('sexo');
        $talla = $request->query->get('talla');
        $color = $request->query->get('color');

        $productos = $productoRepository->searchAndFilter($nombre, $tipo, $sexo, $talla, $color);
        return $this->json($productos);
    }

    #[Route('/all', name: 'productos_all')]
    public function index(): JsonResponse
    {
        $productos = $this->productosService->getAllProductos();

        // Ensure $productos is an array of Productos objects
        $productosArray = array_map(function (Productos $producto) {
            return [
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'descripcion' => $producto->getDescripcion(),
                'tipo' => $producto->getTipo(),
                'precio' => $producto->getPrecio(),
                'imagenes' => json_decode($producto->getImagen(), true),
                'sexo' => $producto->getSexo(),
                'talla' => $producto->getTalla() ? [
                    'id' => $producto->getTalla()->getId(),
                    'descripcion' => $producto->getTalla()->getDescripcion()
                ] : null,
                'color' => $producto->getColor() ? [
                    'id' => $producto->getColor()->getId(),
                    'descripcion' => $producto->getColor()->getDescripcion()
                ] : null,            ];
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
        $talla = $data['talla'] ?? null;
        $color = $data['color'] ?? null;

        // Verificar que todos los campos obligatorios están presentes
        if (!$nombre || !$descripcion || !$tipoStr || !$precio || !$imagen || !$sexoStr || !$talla || !$color) {
            return new JsonResponse(['error' => 'Faltan campos obligatorios'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $talla = $tallasRepository->find($talla);
        $color = $coloresRepository->find($color);

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

        // Convertir la cadena de URLs de imagen en un array
        $imagenesArray = explode(',', $imagen);

        // Crear el producto
        $producto = new Productos();
        $producto->setNombre($nombre);
        $producto->setDescripcion($descripcion);
        $producto->setTipo($tipo);
        $producto->setPrecio((float)$precio);
        $producto->setImagen($imagenesArray); // Pasar el array de imágenes
        $producto->setSexo($sexo);
        $producto->setTalla($talla);
        $producto->setColor($color);

        $productosRepository->save($producto, true);

        return new JsonResponse(['message' => 'Producto creado exitosamente'], JsonResponse::HTTP_CREATED);
    }
    #[Route('/editar/{id}', name: 'productos_edit', methods: ['PUT'],requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            // Verificar que el campo imagen sea un array
            if (isset($data['imagen']) && !is_array($data['imagen'])) {
                return new JsonResponse(['error' => 'El campo imagen debe ser un array'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $producto = $this->productosService->actualizarProducto($id, $data);

            return new JsonResponse([
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'descripcion' => $producto->getDescripcion(),
                'tipo' => $producto->getTipo()->value,
                'precio' => $producto->getPrecio(),
                'imagen' => json_decode($producto->getImagen(), true), // Decodificar el JSON de imágenes
                'sexo' => $producto->getSexo()->value,
                'talla' => $producto->getTalla() ? $producto->getTalla()->getId() : null,
                'color' => $producto->getColor() ? $producto->getColor()->getId() : null,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/eliminar/{id}', name: 'eliminar_producto', methods: ['DELETE'])]
    public function eliminar(int $id): JsonResponse
    {
        try {
            // Intentar eliminar el producto usando el servicio
            $this->productosService->eliminarProducto($id);

            // Si todo sale bien, devolver un mensaje de éxito
            return new JsonResponse(['mensaje' => 'Producto eliminado con éxito.'], JsonResponse::HTTP_OK);
        } catch (\NotFoundHttpException $e) {
            // Si no se encuentra el producto, devolver error 404
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            // Si ocurre cualquier otro error, devolver error 400
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

    #[Route('/tipos', name: 'get_tipos', methods: ['GET'])]
    public function getTipos(): JsonResponse
    {
        $tipos = array_map(fn($tipo) => $tipo->value, Tipo::cases());
        return new JsonResponse($tipos);
    }

    #[Route('/sexos', name: 'sexos_all', methods: ['GET'])]
    public function getSexos(): JsonResponse
    {
        // Obtener los valores del enum Sexo
        $sexos = array_map(fn($sexo) => $sexo->value, Sexo::cases());

        return new JsonResponse($sexos, JsonResponse::HTTP_OK);
    }
}