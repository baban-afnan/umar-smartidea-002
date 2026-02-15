<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmeData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SmeDataController extends Controller
{
    /**
     * Display a listing of the SME data plans.
     */
    public function index(Request $request)
    {
        $query = SmeData::query();

        if ($request->filled('network')) {
            $query->where('network', $request->network);
        }
        if ($request->filled('data_id')) {
            $query->where('data_id', 'like', '%' . $request->data_id . '%');
        }
        if ($request->filled('size')) {
            $query->where('size', 'like', '%' . $request->size . '%');
        }
        if ($request->filled('validity')) {
            $query->where('validity', 'like', '%' . $request->validity . '%');
        }

        $variations = $query->latest()->paginate(20)->withQueryString();

        // Get unique values for filters
        $networks = SmeData::distinct()->pluck('network');
        
        return view('admin.data-variations.sme-data', compact('variations', 'networks'));
    }

    /**
     * Sync SME data plans from external API.
     */
    public function sync()
    {
        try {
            $response = Http::get('https://api.arewasmart.com.ng/api/v1/sme-data/variations');

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
                    foreach ($data['data'] as $plan) {
                        SmeData::updateOrCreate(
                            ['data_id' => $plan['data_id']],
                            [
                                'network' => $plan['network'],
                                'plan_type' => $plan['plan_type'],
                                'amount' => $plan['amount'],
                                'size' => $plan['size'],
                                'validity' => $plan['validity'],
                                'status' => true,
                            ]
                        );
                    }
                    return back()->with('success', 'SME Data Plans synced successfully.');
                }
            }
            
            return back()->with('error', 'Failed to fetch data from API.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SmeData $smeData)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $smeData->update($validated);

        return back()->with('success', 'Plan amount updated successfully.');
    }
}
