<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Stock;
use App\Repositories\MedicineRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class MedicineController extends Controller
{
    protected $medicineRepository;

    public function __construct(MedicineRepository $medicineRepository)
    {
        $this->medicineRepository = $medicineRepository;
    }

    /**
     * Serve the Blade template for adding medicine or analyzing images.
     *
     * @return \Illuminate\View\View
     */
    public function showAddForm()
    {
        return view('medicine.add');
    }
    
    public function addMedicine(Request $request)
    {
        $request->validate([
            'medicine_name' => 'required|string|max:255',
            'confirm_save' => 'required|boolean'
        ]);

        $user = Auth::user();
        $medicineName = $request->input('medicine_name');
        $confirmSave = $request->input('confirm_save');

        // Fetch medicine details from Gemini
        $details = $this->medicineRepository->fetchMedicineDetails($medicineName);

        if (!$details) {
            return response()->json(['error' => 'Failed to fetch medicine details'], 500);
        }

        // Return details for user confirmation if not confirmed
        if (!$confirmSave) {
            return response()->json(['details' => $details, 'message' => 'Please confirm to save']);
        }

        $data = [
            'generic_name'   => is_array($details['generic_name']) ? implode(', ', $details['generic_name']) : $details['generic_name'],
            'brand_names'    => is_array($details['brand_names']) ? implode(', ', $details['brand_names']) : $details['brand_names'],
            'uses'           => is_array($details['uses']) ? json_encode($details['uses']) : $details['uses'],
            'dosage'         => is_array($details['dosage']) ? implode(', ', $details['dosage']) : $details['dosage'],
            'side_effects'   => is_array($details['side_effects']) ? implode(', ', $details['side_effects']) : $details['side_effects'],
            'precautions'    => is_array($details['precautions']) ? json_encode($details['precautions']) : $details['precautions'],
            'user_id'        => $user->id ?? null,
        ];

        // if ($user->role === 'patient') {
        try{
            $medicine = Medicine::create($data);
        } catch(Exception $e){
            Log::info($data);
        }
            return response()->json(['message' => 'Medicine added to Medicine Box',
            'data' => $medicine
            ]);
        // } elseif ($user->role === 'shopkeeper') {
        //     Stock::create([
        //         'generic_name' => $details['generic_name'],
        //         'brand_names' => $details['brand_names'],
        //         'uses' => $details['uses'],
        //         'dosage' => $details['dosage'],
        //         'side_effects' => $details['side_effects'],
        //         'precautions' => $details['precautions'],
        //         'shopkeeper_id' => $user->id,
        //         'quantity' => $request->input('quantity', 0),
        //     ]);
        //     return response()->json(['message' => 'Medicine added to Stock']);
        // }

        return response()->json(['error' => 'Invalid user role'], 403);
    }
}