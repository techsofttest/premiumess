<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleRoutesService
{
    /**
     * Optimize the sequence of delivery orders.
     *
     * @param array $origin ['lat' => float, 'lng' => float]
     * @param array $destinations Array of elements like ['id' => mixed, 'lat' => float, 'lng' => float]
     * @return array Array containing 'destinations', 'distance_km', and 'duration_minutes'
     */
    public function optimizeRoute(array $origin, array $destinations): array
    {
        if (empty($destinations)) {
            return [
                'destinations' => [],
                'distance_km' => 0,
                'duration_minutes' => 0,
            ];
        }

        // 1. Optimize the stop sequence locally using Heuristic (Nearest-Neighbor) engine
        $heuristicResult = $this->optimizeWithHeuristic($origin, $destinations);
        $optimizedStops = $heuristicResult['destinations'];

        $apiKey = config('services.google.maps_api_key');

        if ($apiKey && !empty($optimizedStops)) {
            try {
                // 2. Fetch road-following path and metrics from Google Routes API for the exact sequence
                return $this->getRouteGeometry($origin, $optimizedStops, $apiKey);
            } catch (\Exception $e) {
                Log::warning('Google Routes API call failed: ' . $e->getMessage() . '. Falling back to heuristic.');
            }
        }

        return $heuristicResult;
    }

    /**
     * Fetch road path geometry and statistics using Google Routes API (computeRoutes) without reordering stops.
     */
    protected function getRouteGeometry(array $origin, array $optimizedDestinations, string $apiKey): array
    {
        $originStr = "{$origin['lat']},{$origin['lng']}";
        
        $waypointParts = [];
        foreach ($optimizedDestinations as $dest) {
            $waypointParts[] = "{$dest['lat']},{$dest['lng']}";
        }
        
        // Pass waypoints without optimize:true to respect our local heuristic sequence
        $waypointsStr = implode('|', $waypointParts);

        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
            'origin' => $originStr,
            'destination' => $originStr, // round-trip
            'waypoints' => $waypointsStr,
            'key' => $apiKey,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (($data['status'] ?? '') === 'OK' && !empty($data['routes'][0])) {
                $route = $data['routes'][0];
                $legs = $route['legs'] ?? [];
                $encodedPolyline = $route['overview_polyline']['points'] ?? null;

                $totalDistanceMeters = 0;
                $totalDurationSeconds = 0;
                foreach ($legs as $leg) {
                    $totalDistanceMeters += $leg['distance']['value'] ?? 0;
                    $totalDurationSeconds += $leg['duration']['value'] ?? 0;
                }

                // Re-calculate ETAs based on leg durations
                $currentTime = now()->startOfHour()->addHours(9); // Start delivery at 9:00 AM
                $finalDestinations = [];
                foreach ($optimizedDestinations as $seq => $dest) {
                    $legDuration = $legs[$seq]['duration']['value'] ?? 600; // in seconds
                    // Add buffer time (e.g., 5 mins per stop)
                    $currentTime = $currentTime->addSeconds($legDuration + 300);

                    $finalDestinations[] = array_merge($dest, [
                        'stop_sequence' => $seq + 1,
                        'eta' => $currentTime->format('h:i A'),
                    ]);
                }

                return [
                    'destinations' => $finalDestinations,
                    'distance_km' => round($totalDistanceMeters / 1000, 2),
                    'duration_minutes' => (int)round($totalDurationSeconds / 60),
                    'encoded_polyline' => $encodedPolyline,
                ];
            }
        }

        throw new \Exception('Invalid Directions API response: ' . $response->body());
    }

    /**
     * Heuristic Nearest-Neighbor Optimization (fallback)
     */
    protected function optimizeWithHeuristic(array $origin, array $destinations): array
    {
        $unvisited = $destinations;
        $currentPoint = $origin;
        $optimized = [];
        $seq = 1;
        $currentTime = now()->startOfHour()->addHours(9); // Start at 9:00 AM

        $totalDistance = 0.0;
        $totalDuration = 0;

        while (!empty($unvisited)) {
            $nearestIndex = null;
            $minDistance = null;

            foreach ($unvisited as $index => $dest) {
                $dist = $this->calculateDistance(
                    $currentPoint['lat'], $currentPoint['lng'],
                    $dest['lat'], $dest['lng']
                );

                if ($minDistance === null || $dist < $minDistance) {
                    $minDistance = $dist;
                    $nearestIndex = $index;
                }
            }

            if ($nearestIndex !== null) {
                $nearestDest = $unvisited[$nearestIndex];
                
                // Assume average speed of 30 km/h: time (mins) = (distance / 30) * 60 = distance * 2
                $travelTimeMins = max(3, round($minDistance * 2));
                // Add 5 minutes buffer for delivery
                $currentTime = $currentTime->addMinutes($travelTimeMins + 5);

                $optimized[] = array_merge($nearestDest, [
                    'stop_sequence' => $seq++,
                    'eta' => $currentTime->format('h:i A'),
                ]);

                $totalDistance += $minDistance;
                $totalDuration += ($travelTimeMins + 5);

                $currentPoint = $nearestDest;
                unset($unvisited[$nearestIndex]);
            } else {
                break;
            }
        }

        if (!empty($optimized)) {
            $lastDest = end($optimized);
            $returnDist = $this->calculateDistance(
                $lastDest['lat'], $lastDest['lng'],
                $origin['lat'], $origin['lng']
            );
            $returnTimeMins = max(3, round($returnDist * 2));
            $totalDistance += $returnDist;
            $totalDuration += $returnTimeMins;
        }

        return [
            'destinations' => $optimized,
            'distance_km' => round($totalDistance, 2),
            'duration_minutes' => (int)$totalDuration,
        ];
    }

    /**
     * Calculate geodesic distance (Haversine formula) in kilometers
     */
    protected function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
