<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AdminWalletController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query()
            ->whereIn('type', ['manual_credit', 'manual_debit']);

        // Filter by Type
        if ($request->filled('type') && in_array($request->type, ['manual_credit', 'manual_debit'])) {
            $query->where('type', $request->type);
        }

        // Filter by Date Range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Get totals with same filters
        $total_manual_credit = (clone $query)->where('type', 'manual_credit')->sum('amount');
        $total_manual_debit = (clone $query)->where('type', 'manual_debit')->sum('amount');

        // Monthly Stats (Current Month)
        $monthly_manual_credit = Transaction::where('type', 'manual_credit')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        $monthly_manual_debit = Transaction::where('type', 'manual_debit')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Palmpay Balance from Cache
        $palmpayBalance = \Illuminate\Support\Facades\Cache::get('palmpay_gateway_balance', 0);

        // Get transactions
        $transactions = $query->with('user')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('adminwallet.index', [
            'transactions' => $transactions,
            'monthly_manual_credit' => $total_manual_credit,
            'monthly_manual_debit' => $total_manual_debit,
            'monthlyFunding' => $monthly_manual_credit,
            'monthlyDebit' => $monthly_manual_debit,
            'palmpayBalance' => $palmpayBalance,
        ]);
    }

    public function fundView()
    {
        $users = User::select('id', 'first_name', 'last_name', 'email', 'phone_no')
            ->with(['wallet:user_id,balance'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone_no' => $user->phone_no,
                    'balance' => $user->wallet->balance ?? 0
                ];
            });
            
        return view('adminwallet.fund', compact('users'));
    }

    public function fund(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:manual_credit,manual_debit',
            'description' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($request->user_id);
        $wallet = $user->wallet;

        if (!$wallet) {
            // Create wallet if it doesn't exist (failsafe)
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'available_balance' => 0,
                'wallet_number' => $user->phone_no ?? Str::random(10),
            ]);
        }

        if ($request->type === 'manual_debit' && $wallet->available_balance < $request->amount) {
            return redirect()->back()->with('error', 'User have insufficient balance');
        }

        DB::transaction(function () use ($wallet, $request, $user) {
            if ($request->type === 'manual_credit') {
                $wallet->increment('balance', $request->amount);
                $wallet->increment('available_balance', $request->amount);
                $transactionType = 'manual_credit';
            } else {
                $wallet->decrement('balance', $request->amount);
                $wallet->decrement('available_balance', $request->amount);
                $transactionType = 'manual_debit';
            }

           $reference = 'MNf' . str_pad(random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'type' => $transactionType,
                'status' => 'completed',
                'transaction_ref' => $reference,
                'referenceId' => $reference,
                'payer_name' => 'Admin',
                'fee' => 0,
                'net_amount' => $request->amount,
                'performed_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'description' => $request->description ?? ucfirst(str_replace('manual_', '', $request->type)) . ' by Admin',
                'metadata' => [
                    'admin_id' => Auth::id(),
                    'admin_name' => Auth::user()->name ?? 'Admin',
                ],
            ]);
        });

        $message = $request->type === 'manual_credit' ? 'Wallet funded successfully.' : 'Wallet debited successfully.';
        return redirect()->route('admin.wallet.index')->with('success', $message);
    }

    public function bulkFundView()
    {
        return view('adminwallet.bulk-fund');
    }

    public function bulkFund(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:manual_credit,manual_debit',
            'description' => 'nullable|string|max:255',
        ]);

        $amount = $request->amount;
        $type = $request->type;
        $description = $request->description ?? ucfirst($type) . ' all users by Admin';
        $adminId = Auth::id();

        // Dispatch job or handle directly if "fast" requirement allows direct SQL
        // Direct SQL is fastest for "all users"
        
        DB::transaction(function () use ($amount, $type, $description, $adminId) {
            $transactionType = $type;
            
            User::with('wallet')->chunkById(1000, function ($users) use ($amount, $type, $description, $adminId, $transactionType) {
                $userIdsToUpdate = [];
                $transactions = [];

                foreach ($users as $user) {
                    $wallet = $user->wallet;
                    
                    // Credit all users, or debit users with sufficient balance
                    if ($type === 'manual_credit' || ($type === 'manual_debit' && $wallet && $wallet->balance >= $amount)) {
                        $userIdsToUpdate[] = $user->id;

                        $reference = 'AF1-' . str_pad(random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
                        $transactions[] = [
                            'user_id' => $user->id,
                            'amount' => $amount,
                            'type' => $transactionType,
                            'status' => 'completed',
                            'transaction_ref' => $reference,
                            'referenceId' => $reference,
                            'payer_name' => 'Admin',
                            'fee' => 0,
                            'net_amount' => $amount,
                            'performed_by' => $adminId,
                            'approved_by' => $adminId,
                            'description' => $description,
                            'metadata' => json_encode([
                                'admin_id' => $adminId,
                                'is_bulk' => true
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($userIdsToUpdate)) {
                    if ($type === 'manual_credit') {
                        Wallet::whereIn('user_id', $userIdsToUpdate)->update([
                            'balance' => DB::raw("balance + $amount"),
                            'available_balance' => DB::raw("available_balance + $amount")
                        ]);
                    } else {
                        Wallet::whereIn('user_id', $userIdsToUpdate)->update([
                            'balance' => DB::raw("balance - $amount"),
                            'available_balance' => DB::raw("available_balance - $amount")
                        ]);
                    }
                    Transaction::insert($transactions);
                }
            });
        });

        return redirect()->route('admin.wallet.index')->with('success', 'All users wallets updated successfully.');
    }
}
