<?php

namespace App\Repositories;

use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PatientAnalysisRepository
{
    /**
     * Analyze an uploaded image using Gemini API.
     *
     * @param string $imagePath Path to the uploaded image in storage
     * @param string $prompt Custom prompt for analysis
     * @return array Analysis result with text and metadata
     * @throws \Exception If analysis fails
     */
    public function analyzeImage(string $imagePath, string $symptoms = null): array
    {
        
        // Provide detailed information about the medicine {$medicineName} in a structured JSON format
        $prompt = "Analyze this image to identify patient symptoms \"{$symptoms}\". Ensure the response is medically accurate and clear. Provide a structured JSON response only with the following fields: symptoms (array of strings), possible_conditions (array of strings), recommended_actions (array of strings), suggested_generic_medicine (array of strings). Use this template:\n\n" .
            json_encode([
                'symptoms' => '',
                'possible_conditions' => [],
                'recommended_actions' => [],
                // 'suggested_generic_medicine' => [
                //     'generic_name' => '',
                //     'brand_names' => [],
                //     'uses' => [],
                //     'dosage' => '',
                //     'side_effects' => [],
                //     'precautions' => []
                // ]
            ], JSON_PRETTY_PRINT);
        
// Return ONLY the JSON object, without any markdown, code fences, or additional text. Include the generic name, brand names (if any), uses, dosage, side effects, and precautions. Use this template:\n\n
        try {
            $imageData = Storage::disk('local')->get($imagePath);
            $mimeType = $this->getMimeType($imagePath);

            if (!in_array($mimeType, [MimeType::IMAGE_JPEG, MimeType::IMAGE_PNG])) {
                throw new \Exception('Unsupported image format. Only JPEG and PNG are allowed.');
            }

            $result = Gemini::generativeModel(model: 'gemini-2.0-flash')
                ->generateContent([
                    $prompt,
                    new Blob(
                        mimeType: $mimeType,
                        data: base64_encode($imageData)
                    )
                ]);

            // $text = $result->text();

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
                    'response' => $rawResponse,
                    'json_error' => json_last_error_msg()
                ]);
                return null;
            }

            // Validate required fields
            $requiredFields = ['symptoms', 'possible_conditions', 'recommended_actions']; //  'suggested_generic_medicine'
            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $response)) {
                    Log::error('Missing required field in Gemini API response', [
                        'field' => $field,
                        'response' => $response
                    ]);
                    return null;
                }
            }

            return $response;

            // return [
            //     'analysis_text' => $text,
            //     'image_path' => $imagePath,
            //     'prompt' => $prompt,
            //     'analyzed_at' => now()->toDateTimeString(),
            // ];
        } catch (\Exception $e) {
            \Log::error('Image analysis failed: ' . $e->getMessage(), ['image_path' => $imagePath]);
            throw new \Exception('Failed to analyze image: ' . $e->getMessage());
        }
    }

    /**
     * Get MIME type based on file extension.
     *
     * @param string $path
     * @return string
     */
    protected function getMimeType(string $path): MimeType
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($extension) {
            'jpg', 'jpeg' => MimeType::IMAGE_JPEG,
            'png' => MimeType::IMAGE_PNG,
            default => throw new \Exception('Invalid image extension'),
        };
    }

    /**
     * Store uploaded image and return its path.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    public function storeImage($image): string
    {
        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
        return $image->storeAs('patient_analyses', $filename, 'local');
    }
}