<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\ProcessTransaction;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentController extends Controller
{
    protected $middleware = [];
    
    public function __construct()
    {
        $this->middleware('throttle:10,1'); // Maksimal 10 requests per menit
    }

    public function createTransaction(StoreTransactionRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validatedData = $request->validated();
        
            $transaction = Transaction::create([
                'user_id' => $validatedData['user_id'],
                'amount' => $validatedData['amount'],
                'status' => Transaction::STATUS_PENDING,
            ]);
        
            // Tambahkan transaksi ke dalam queue untuk diproses
            ProcessTransaction::dispatch($transaction);
            
            DB::commit();
        
            return response()->json($transaction, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan transaksi'], 500);
        }
    }

    public function processPayment(UpdateTransactionRequest $request, $transactionId)
    {
        DB::beginTransaction();
        
        try {
            $transaction = Transaction::findOrFail($transactionId);
            $validatedData = $request->validated();
            
            $transaction->update([
                'status' => $validatedData['status'],
            ]);
            
            DB::commit();
            
            return response()->json($transaction, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal memproses transaksi'], 500);
        }
    }

    public function getUserTransactions(Request $request, $userId)
    {
        $cacheKey = 'user_transactions_' . $userId;
        $minutes = 10; // Contoh: cache valid selama 10 menit

        return response()->json(
            Cache::remember($cacheKey, $minutes, function () use ($userId) {
                $transactions = Transaction::where('user_id', $userId)->paginate(10);
                return $transactions;
            })
        );
    }

    public function getTransactionDataSummary()
    {
        $totalTransactions = Transaction::count();
        $averageAmount = Transaction::avg('amount');
        $highestTransaction = Transaction::orderBy('amount', 'desc')->first();
        $lowestTransaction = Transaction::orderBy('amount', 'asc')->first();
        $longestNameTransaction = Transaction::with('user')->get()->sortByDesc(function ($transaction) {
            return strlen($transaction->user->name);
        })->first();

        $statusDistribution = Transaction::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return response()->json([
            'total_transactions' => $totalTransactions,
            'average_amount' => $averageAmount,
            'highest_transaction' => $highestTransaction,
            'lowest_transaction' => $lowestTransaction,
            'longest_name_transaction' => [
                'id' => $longestNameTransaction->id,
                'user_id' => $longestNameTransaction->user_id,
                'amount' => $longestNameTransaction->amount,
                'status' => $longestNameTransaction->status,
                'created_at' => $longestNameTransaction->created_at,
                'updated_at' => $longestNameTransaction->updated_at,
                'user_name' => $longestNameTransaction->user->name,
            ],
            'status_distribution' => $statusDistribution,
        ]);
    }
}
