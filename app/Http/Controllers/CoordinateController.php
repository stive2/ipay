<?php

namespace App\Http\Controllers;

use App\Events\Agent\AgentCoordinatesUpdated;
use App\Models\Agent;
use App\Models\Coordinate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CoordinateController extends Controller
{
    public function index()
    {
        $agents = Agent::with('latestCoordinate')->get()->toArray();

        return response()->json([
            'data' => array_values($agents)
        ], 200);
    }

    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'id' => 'required',
        ]);

        function generateRandomFloat($min = -0.01, $max = 0.01)
        {
            return mt_rand($min * 100, $max * 100) / 100;
        }

        // Utilisation
        $randomFloat = generateRandomFloat();

        // Création de la coordonnée
        $coordinate = Coordinate::create([
            'latitude' => $validatedData['latitude'] + $randomFloat,
            'longitude' => $validatedData['longitude'],
            'agent_id' => $validatedData['id'], // Auth::id(),
        ]);

        event(new AgentCoordinatesUpdated($coordinate));
        //AgentCoordinatesUpdated::dispatch("nouvelle coordonnée");

        // Retourner une réponse
        return response()->json([
            'message' => 'Coordonnée enregistrée avec succès',
            'coordinate' => $coordinate,
        ], 201);
    }
}
