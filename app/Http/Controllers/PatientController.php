<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Stock;
use App\Repositories\PatientAnalysisRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PatientController extends Controller
{
    protected $patientAnalysisRepository;

    public function __construct(PatientAnalysisRepository $patientAnalysisRepository)
    {
        $this->patientAnalysisRepository = $patientAnalysisRepository;
    }

    /**
     * Handle image upload and analysis via Gemini API.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeImage(Request $request)
    {

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png|max:2048',
            'symptoms' => 'nullable|string|max:500',
        ]);

        try {
            $user = Auth::user();
            $image = $request->file('image');
            $symptoms = $request->input('symptoms', null);

            $imagePath = $this->patientAnalysisRepository->storeImage($image);
// dd($imagePath);
            $analysis = $this->patientAnalysisRepository->analyzeImage($imagePath, $symptoms);
// output:
//             array:4 [ // app/Http/Controllers/PatientController.php:44
//   "symptoms" => array:4 [
//     0 => "Raised skin welts (wheals)"
//     1 => "Redness of skin"
//     2 => "Itchy skin"
//     3 => "Possible swelling"
//   ]
//   "possible_conditions" => array:4 [
//     0 => "Urticaria (Hives)"
//     1 => "Angioedema"
//     2 => "Allergic reaction"
//     3 => "Dermatographism"
//   ]
//   "recommended_actions" => array:4 [
//     0 => "Identify and avoid potential triggers (foods, medications, environmental factors)."
//     1 => "Apply cool compresses to the affected area."
//     2 => "Consult a doctor for diagnosis and treatment options."
//     3 => "Monitor for signs of a severe allergic reaction (difficulty breathing, swelling of the face/throat) and seek immediate medical attention if present."
//   ]
// ]

            // $patientAnalysis = PatientAnalysis::create([
            //     'user_id' => $user->id,
            //     'image_path' => $imagePath,
            //     'analysis_text' => $analysis['analysis_text'],
            //     'prompt' => $prompt,
            //     'analyzed_at' => $analysis['analyzed_at'],
            // ]);

            return response()->json([
                'message' => 'Image analyzed successfully',
                'analysis' => [
                    'id' => $patientAnalysis->id ?? null,
                    'image_path' => $imagePath,
                    'prompt' => $symptoms,
                    'analysis' => $analysis,
                    // 'analyzed_at' => $analysis['analyzed_at'],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Image analysis failed: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}