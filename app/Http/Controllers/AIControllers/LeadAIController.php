<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeadAIController extends Controller
{
    public function enhanceMessage(Request $request)
    {
        try {
            // Validate that we have a message
            if (!$request->has('message')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No message provided'
                ], 400);
            }

            // Check if we have the API token configured
            $token = config('services.huggingface.token');
            if (empty($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hugging Face API token not configured'
                ], 500);
            }

            // Log the request for debugging
            \Log::info('Making request to Hugging Face API', [
                'message' => $request->message
            ]);

            $response = Http::withoutVerifying() // Skip SSL verification
                ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post('https://router.huggingface.co/v1/chat/completions', [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Enhance this business message professionally and make it more polite and formal: ' . $request->message
                    ]
                ],
                'model' => 'meta-llama/Llama-3.1-8B-Instruct:novita',
                'stream' => false,
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);

            // Log the response for debugging
            \Log::info('Hugging Face API response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // Check if response has the expected structure
                if (isset($result['choices'][0]['message']['content'])) {
                    $enhancedMessage = $result['choices'][0]['message']['content'];
                    
                    // Clean up the response if needed
                    $enhancedMessage = trim($enhancedMessage);
                    
                    return response()->json([
                        'success' => true,
                        'enhanced_message' => $enhancedMessage
                    ]);
                }
                
                throw new \Exception('Invalid response format from API: ' . json_encode($result));
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to enhance message. Status: ' . $response->status() . ', Error: ' . ($response->body() ?: 'Unknown error'),
            ], 500);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in LeadAIController', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'debug_info' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }
}