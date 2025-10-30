<?php

namespace App\Http\Controllers\AIControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Log;

class LeaveAIController extends Controller
{
    public function enhanceLeaveApplication(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'leave_type' => 'required|string',
                'reason' => 'required|string'
            ]);

            // Format dates for better readability
            $fromDate = Carbon::parse($request->start_date)->format('F j, Y');
            $toDate = Carbon::parse($request->end_date)->format('F j, Y');
            $numberOfDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

            // Construct the prompt for the AI
            $prompt = "Please enhance this leave application professionally. Format a polite and formal leave request with the following details:\n" .
                     "- Leave Type: {$request->leave_type}\n" .
                     "- Duration: {$numberOfDays} day(s) from {$fromDate} to {$toDate}\n" .
                     "- Reason: {$request->reason}\n\n" .
                     "Make it professional, polite, and include all necessary details while maintaining formality.";

            // Check if we have the API token configured
            $token = config('services.huggingface.token');
            if (empty($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hugging Face API token not configured'
                ], 500);
            }

            // Log the request for debugging
            \Log::info('Making leave enhancement request to Hugging Face API', [
                'prompt' => $prompt
            ]);

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])->post('https://router.huggingface.co/v1/chat/completions', [
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'model' => 'meta-llama/Llama-3.1-8B-Instruct:novita',
                    'stream' => false,
                    'temperature' => 0.7,
                    'max_tokens' => 500
                ]);

            if (!$response->successful()) {
                \Log::error('Error from Hugging Face API', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to enhance leave application. API error.'
                ], 500);
            }

            $enhancedMessage = $response->json()['choices'][0]['message']['content'] ?? null;

            Log::info('Received enhanced leave application from Hugging Face API', [
                'enhanced_message' => $enhancedMessage
            ]);

            if (!$enhancedMessage) {
                return response()->json([
                    'success' => false,
                    'message' => 'No enhanced message received from API'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'enhanced_message' => $enhancedMessage
            ]);

            

           
        } catch (\Exception $e) {
            \Log::error('Error in LeaveAIController@enhanceLeaveApplication', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to enhance leave application: ' . $e->getMessage()
            ], 500);
        }
    }
}
