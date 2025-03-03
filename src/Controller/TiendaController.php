<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TiendaController extends AbstractController
{
    #[Route('/tiendas', name: 'get_tiendas', methods: ['GET'])]
    public function getTiendas(): JsonResponse
    {
        $tiendas = [
            // Madrid
            ["name" => "Tienda Madrid 1", "lat" => 40.4168, "lng" => -3.7038],
            ["name" => "Tienda Madrid 2", "lat" => 40.4200, "lng" => -3.7058],

            // Barcelona
            ["name" => "Tienda Barcelona 1", "lat" => 41.3879, "lng" => 2.16992],
            ["name" => "Tienda Barcelona 2", "lat" => 41.3900, "lng" => 2.1700],
            ["name" => "Tienda Barcelona 3", "lat" => 41.3950, "lng" => 2.1720],

            // Sevilla
            ["name" => "Tienda Sevilla 1", "lat" => 37.3886, "lng" => -5.9823],
            ["name" => "Tienda Sevilla 2", "lat" => 37.3920, "lng" => -5.9840],
            ["name" => "Tienda Sevilla 3", "lat" => 37.3950, "lng" => -5.9860],
            ["name" => "Tienda Sevilla 4", "lat" => 37.4000, "lng" => -5.9900],

            // Alemania
            ["name" => "Tienda Alemania 1", "lat" => 52.5200, "lng" => 13.4050], // Berlín
            ["name" => "Tienda Alemania 2", "lat" => 48.1351, "lng" => 11.5820], // Múnich
        ];

        return $this->json($tiendas);
    }
}
