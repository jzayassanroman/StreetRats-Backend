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
    #[Route('/all', name: 'productos_all')]
    public function index(): JsonResponse
    {
        // Usamos el servicio para obtener los productos
        $productos = $this->productosService->getAllProductos();

        return new JsonResponse($productos);
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
        $idTalla = $data['id_talla'] ?? null;
        $idColor = $data['id_color'] ?? null;

        // Verificar que todos los campos obligatorios están presentes
        if (!$nombre || !$descripcion || !$tipoStr || !$precio || !$imagen || !$sexoStr || !$idTalla || !$idColor) {
            return new JsonResponse(['error' => 'Faltan campos obligatorios'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $talla = $tallasRepository->find($idTalla);
        $color = $coloresRepository->find($idColor);

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
        $producto->setIdTalla($talla);
        $producto->setIdColor($color);

        $productosRepository->save($producto, true);

        return new JsonResponse(['message' => 'Producto creado exitosamente'], JsonResponse::HTTP_CREATED);
    }
    #[Route('/editar/{id}', name: 'productos_edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $producto = $this->productosService->actualizarProducto($id, $data);

            return new JsonResponse([
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'descripcion' => $producto->getDescripcion(),
                'tipo' => $producto->getTipo()->value,
                'precio' => $producto->getPrecio(),
                'imagen' => $producto->getImagen(),
                'sexo' => $producto->getSexo()->value,
                'id_talla' => $producto->getIdTalla() ? $producto->getIdTalla()->getId() : null,
                'id_color' => $producto->getIdColor() ? $producto->getIdColor()->getId() : null,
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