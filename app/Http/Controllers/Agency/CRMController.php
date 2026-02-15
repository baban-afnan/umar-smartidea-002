<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\AgentService;
use App\Models\User;
use App\Models\ServiceField;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CRMController extends Controller
{
    /**
     * List CRM requests with filters and pagination
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $bankFilter = $request->input('bank');

        // Base query filtering by service_type
        $query = AgentService::query()
            ->where('service_type', 'CRM');

        // Enhanced search: BVN, NIN, transaction_ref, agent name
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ticket_id', 'like', "%$search%")
                  ->orWhere('batch_id', 'like', "%$search%")
                  ->orWhere('reference', 'like', "%$search%")
                  ->orWhere('performed_by', 'like', "%$search%")
                  ->orWhere('user_id', 'like', "%$search%");
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($bankFilter) {
            $query->where('bank', $bankFilter);
        }

        // Apply custom status order + submission_date
        $enrollments = $query
            ->orderByRaw("CASE status
                WHEN 'pending' THEN 1
                WHEN 'processing' THEN 2
                WHEN 'in-progress' THEN 3
                WHEN 'query' THEN 4
                WHEN 'resolved' THEN 5
                WHEN 'successful' THEN 6
                WHEN 'rejected' THEN 7
                WHEN 'failed' THEN 8
                WHEN 'remark' THEN 9
                ELSE 999 END")
            ->orderByDesc('submission_date')
            ->paginate(10);

        // Status counts filtered by service_type
        $statusCounts = [
            'pending'    => AgentService::where('service_type', 'CRM')->where('status', 'pending')->count(),
            'processing' => AgentService::where('service_type', 'CRM')->where('status', 'processing')->count(),
            'resolved'   => AgentService::where('service_type', 'CRM')->whereIn('status', ['resolved', 'successful'])->count(),
            'rejected'   => AgentService::where('service_type', 'CRM')->whereIn('status', ['rejected', 'failed'])->count(),
        ];

        // Get distinct banks for filter
        $banks = $this->getDistinctBanks();

        return view('crm.crm', compact('enrollments', 'search', 'statusFilter', 'bankFilter', 'statusCounts', 'banks'));
    }

    /**
     * Show details of a single CRM request
     */
    public function show($id)
    {
        $enrollmentInfo = AgentService::findOrFail($id);
        $user = User::find($enrollmentInfo->user_id);

        $statusHistory = collect([
            [
                'status' => $enrollmentInfo->status,
                'comment' => $enrollmentInfo->comment,
                'submission_date' => $enrollmentInfo->created_at,
                'updated_at' => $enrollmentInfo->updated_at,
                'file_url' => $enrollmentInfo->file_url,
            ]
        ]);

        return view('crm.crm-view', compact('enrollmentInfo', 'statusHistory', 'user'));
    }

    /**
     * Update the status of a CRM request
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,in-progress,resolved,successful,rejected,failed,query,remark',
            'comment' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
            'force_refund' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $enrollment = AgentService::findOrFail($id);
            $oldStatus = $enrollment->status;
            $user = User::find($enrollment->user_id);

            // Handle file upload
            $fileUrl = $enrollment->file_url;
            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($fileUrl && Storage::disk('public')->exists($fileUrl)) {
                    Storage::disk('public')->delete($fileUrl);
                }

                // Store new file
                $file = $request->file('file');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('crm-files', $fileName, 'public');
                $baseUrl = rtrim(config('app.url'), '/');
                $fileUrl = $baseUrl . '/storage/crm-files/' . $fileName;
            }

            // Update enrollment
            $enrollment->status = $request->status;
            $enrollment->comment = $request->comment;
            $enrollment->file_url = $fileUrl;
            $enrollment->save();

            // Handle refund logic if rejected
            if ($request->status === 'rejected') {
                if ($oldStatus !== 'rejected' || $request->force_refund) {
                    $this->processRefund($enrollment, $request->force_refund);
                }
            }

            DB::commit();
            return redirect()->route('crm.index')
                ->with('successMessage', 'Status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('crm.index')
                ->with('errorMessage', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Handle refund when a request is rejected
     */
    private function processRefund($enrollment, $forceRefund = false)
    {
        $serviceFieldId = $enrollment->service_field_id;
        $user = User::find($enrollment->user_id);

        if (!$user) {
            throw new \Exception('User not found.');
        }

        if (!$serviceFieldId) {
            throw new \Exception('Service field ID is missing.');
        }

        $serviceField = ServiceField::find($serviceFieldId);

        if (!$serviceField) {
            throw new \Exception('Service field not found.');
        }

        $role = strtolower($user->role ?? 'default');

        // Check if refund already exists
        $refundExists = Transaction::where('type', 'refund')
            ->where('description', 'LIKE', "%Request ID #{$enrollment->id}%")
            ->exists();

        if ($refundExists && !$forceRefund) {
            throw new \Exception('Refund already processed for this request.');
        }

        // Fetch price for role, fallback to base price
        $servicePrice = DB::table('service_prices')
            ->where('service_field_id', $serviceFieldId)
            ->where('user_type', $role)
            ->value('price');

        $basePrice = $servicePrice ?: $serviceField->base_price;

        if (!$basePrice || $basePrice <= 0) {
            throw new \Exception('No valid price found for refund.');
        }

        $refundAmount = round($basePrice, 2);
        $debitAmount = 0.00;

        $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

        if (!$wallet) {
            throw new \Exception('Wallet not found for user.');
        }

        // Update wallet balance
        $wallet->balance += $refundAmount;
        $wallet->save();

        // Create refund transaction
        Transaction::create([
            'transaction_ref' => strtoupper(Str::random(12)),
            'user_id' => $user->id,
            'performed_by' => Auth::user()->first_name . ' ' . (Auth::user()->last_name ?? ''),
            'amount' => $refundAmount,
            'fee' => 0.00,
            'net_amount' => $refundAmount,
            'description' => "Refund 100% for rejected service [{$serviceField->field_name}], Request ID #{$enrollment->id}",
            'type' => 'refund',
            'status' => 'completed',
            'metadata' => json_encode([
                'service_id' => $enrollment->service_id,
                'service_field_id' => $serviceFieldId,
                'field_code' => $serviceField->field_code,
                'field_name' => $serviceField->field_name ?? null,
                'user_role' => $role,
                'base_price' => $basePrice,
                'percentage_refunded' => 100,
                'amount_debited_by_system' => 0.00,
                'forced_refund' => $forceRefund,
            ]),
        ]);
    }

    /**
     * Get distinct banks from agent_services table
     */
    private function getDistinctBanks()
    {
        return AgentService::whereNotNull('bank')
            ->where('bank', '!=', '')
            ->distinct()
            ->pluck('bank')
            ->sort()
            ->values();
    }

    /**
     * Export pending CRM requests as CSV
     */
    public function exportCsv()
    {
        $fileName = 'crm-pending-requests-' . date('Y-m-d') . '.csv';
        $enrollments = AgentService::where('service_type', 'CRM')
            ->where('status', 'pending')
            ->select('ticket_id', 'batch_id')
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($enrollments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Ticket ID', 'Batch ID']);

            foreach ($enrollments as $enrollment) {
                fputcsv($file, [$enrollment->ticket_id, $enrollment->batch_id]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export pending CRM requests as Excel
     */
    public function exportExcel()
    {
        $enrollments = AgentService::where('service_type', 'CRM')
            ->where('status', 'pending')
            ->select('ticket_id', 'batch_id')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Ticket ID');
        $sheet->setCellValue('B1', 'Batch ID');

        // Add data
        $row = 2;
        foreach ($enrollments as $enrollment) {
            $sheet->setCellValue('A' . $row, $enrollment->ticket_id);
            $sheet->setCellValue('B' . $row, $enrollment->batch_id);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'B') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'crm-pending-requests-' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName);
    }
}
