<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvitationController extends Controller
{
    public function generateInvitation(Request $request)
    {
        $groom = $request->input('groom');
        $maid = $request->input('maid');
        $religion = $request->input('religion');

        Log::info('Request data', [
            'groom' => $groom,
            'maid' => $maid,
            'religion' => $religion,
        ]);

        $prompt = "Generate a typical Islamic wedding invitation with the following details: Groom: $groom, Maid: $maid, Religion: $religion";

        try {
            Log::info('Sending request to API', [
                'url' => 'http://localhost:11434/api/generate',
                'data' => [
                    'model' => 'qwen',
                    'prompt' => $prompt,
                    'stream' => false,
                ]
            ]);

            $response = Http::timeout(2000)->post('http://localhost:11434/api/generate', [
                'model' => 'qwen',
                'prompt' => $prompt,
                'stream' => false,
            ]);

            Log::info('Full API response', [
                'response' => $response->json() // Menampilkan seluruh respons JSON untuk debug
            ]);

            if ($response->successful()) {
                $result = $response->json(); // Ambil seluruh respons JSON
                $message = $result['response'] ?? 'No content available'; // Sesuaikan kunci dengan struktur respons
                
                return response()->json([
                    'message' => $message,
                ]);
            } else {
                Log::error('API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return response()->json([
                    'error' => 'Failed to generate invitation',
                    'response' => $response->body()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error generating invitation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An error occurred while generating invitation',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
