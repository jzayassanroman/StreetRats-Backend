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

    public function __construct(ProductosService $productosService)
    {
        $this->productosService = $productosService;
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
                'id_talla' => $producto->getIdTalla() ? $producto->getIdTalla()->getId() : null,
                'id_color' => $producto->getIdColor() ? $producto->getIdColor()->getId() : null,
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
                'id_talla' => $producto->getIdTalla() ? $producto->getIdTalla()->getId() : null,
                'id_color' => $producto->getIdColor() ? $producto->getIdColor()->getId() : null,
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


}