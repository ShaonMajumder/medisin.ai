<?php

namespace App\Repositories;

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class MedicineRepository
{
    public function fetchMedicineDetails(string $medicineName): ?array
    {
        try {
            $prompt = "Provide detailed information about the medicine {$medicineName} in a structured JSON format. Return ONLY the JSON object, without any markdown, code fences, or additional text. Include the generic name, brand names (if any), uses, dosage, side effects, and precautions. Use this template:\n\n" .
                json_encode([
                    'generic_name' => '',
                    'brand_names' => [],
                    'uses' => [],
                    'dosage' => '',
                    'side_effects' => [],
                    'precautions' => []
                ], JSON_PRETTY_PRINT);

            $result = Gemini::generativeModel('gemini-2.0-flash')->generateContent($prompt);
            $rawResponse = $result->text();

            // Clean the response to remove markdown or extra text
            $cleanResponse = trim($rawResponse);
            if (str_starts_with($cleanResponse, '```json') || str_starts_with($cleanResponse, '```')) {
                $cleanResponse = preg_replace('/^```json\s*|\s*```$/s', '', $cleanResponse);
            }
            // Remove any non-JSON content before the first { or after the last }
            $cleanResponse = preg_replace('/^.*?(?={)/s', '', $cleanResponse);
            $cleanResponse = preg_replace('/}.*$/s', '}', $cleanResponse);

            // Decode JSON
            $response = json_decode($cleanResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON response from Gemini API', [
                    'medicine' => $medicineName,
                    'response' => $rawResponse,
                    'json_error' => json_last_error_msg()
                ]);
                return null;
            }

            // Validate required fields
            $requiredFields = ['generic_name', 'brand_names', 'uses', 'dosage', 'side_effects', 'precautions'];
            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $response)) {
                    Log::error('Missing required field in Gemini API response', [
                        'medicine' => $medicineName,
                        'field' => $field,
                        'response' => $response
                    ]);
                    return null;
                }
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Gemini API error', [
                'medicine' => $medicineName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}