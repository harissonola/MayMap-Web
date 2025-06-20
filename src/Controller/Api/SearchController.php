<?php

namespace App\Controller\Api;

use App\Repository\EstablishmentRepository;
use App\Repository\TypeEstablishmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\HttpClient\HttpClient;

class SearchController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer
    ) {}

    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function search(
        Request $request,
        EstablishmentRepository $establishmentRepo,
        TypeEstablishmentRepository $typeRepo
    ): JsonResponse {
        $query = $request->query->get('q', '');
        $typeId = $request->query->get('type');
        $latitude = $request->query->get('lat');
        $longitude = $request->query->get('lng');
        $radius = $request->query->get('radius', 5);
        $limit = $request->query->get('limit', 20);

        // Si pas de coordonnées fournies, essayer de géolocaliser l'utilisateur via IP
        if (!$latitude || !$longitude) {
            $ip = $request->getClientIp();
            if ($ip && $ip !== '127.0.0.1') {
                $locationData = $this->getLocationFromIp($ip);
                if ($locationData) {
                    $latitude = $locationData['lat'];
                    $longitude = $locationData['lon'];
                }
            }
        }

        try {
            if ($typeId) {
                $type = $typeRepo->find($typeId);
                if (!$type) {
                    return $this->json([
                        'error' => 'Type d\'établissement non trouvé',
                        'results' => []
                    ], 404);
                }
                $establishments = $establishmentRepo->findByTypeAndLocation($type, $query);
            }
            elseif ($latitude && $longitude) {
                $establishments = $establishmentRepo->findNearby(
                    floatval($latitude),
                    floatval($longitude),
                    floatval($radius),
                    $limit
                );
            }
            else {
                $establishments = $establishmentRepo->findBySearchQuery($query, $limit);
            }

            $results = $this->serializer->normalize($establishments, null, [
                AbstractNormalizer::GROUPS => ['establishment:read'],
                AbstractNormalizer::CALLBACKS => [
                    'distance' => function ($innerObject) {
                        return isset($innerObject) ? round($innerObject, 2) : null;
                    },
                ],
            ]);

            // Ajouter l'URL complète pour les images
            // Ajouter l'URL complète pour les images
            $baseUrl = $request->getSchemeAndHttpHost() . '/establishments/';
            foreach ($results as &$result) {
                if (isset($result['images'])) {  // Parenthèse fermante ajoutée ici
                    foreach ($result['images'] as &$image) {
                        $image['fullUrl'] = $baseUrl . $image['imageUrl'];
                    }
                }
                // Trouver l'image logo si elle existe
                $logoImage = null;
                if (isset($result['images'])) {  // Parenthèse fermante ajoutée ici
                    foreach ($result['images'] as $image) {
                        if ($image['isLogo'] ?? false) {
                            $logoImage = $image;
                            break;
                        }
                    }
                }
                $result['logoImage'] = $logoImage;
            }

            return $this->json([
                'success' => true,
                'count' => count($results),
                'userLocation' => $latitude && $longitude ? [
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ] : null,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Une erreur est survenue lors de la recherche',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getLocationFromIp(string $ip): ?array
    {
        try {
            $client = HttpClient::create();
            $response = $client->request('GET', "http://ip-api.com/json/{$ip}?fields=status,message,lat,lon");

            $data = $response->toArray();
            if ($data['status'] === 'success') {
                return [
                    'lat' => $data['lat'],
                    'lon' => $data['lon']
                ];
            }
        } catch (\Exception $e) {
            // Log l'erreur si nécessaire
        }

        return null;
    }
}