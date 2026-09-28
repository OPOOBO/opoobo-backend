<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function debit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'tx_ref' => 'required|string|max:100',
        ]);

        return $this->move($request, $validated, 'debit');
    }

    public function credit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'tx_ref' => 'required|string|max:100',
        ]);

        return $this->move($request, $validated, 'credit');
    }

    private function move(Request $request, array $validated, string $direction): JsonResponse
    {
        $amount = round((float) $validated['amount'], 2);
        $txRef = $validated['tx_ref'];

        return DB::transaction(function () use ($request, $amount, $txRef, $direction) {
            $user = User::where('id', $request->user()->id)->lockForUpdate()->first();
            $existing = WalletTransaction::where('user_id', $user->id)
                ->where('tx_ref', $txRef)
                ->where('direction', $direction)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'data' => $this->payload($user, $existing->amount, $txRef),
                ]);
            }

            if ($direction === 'credit') {
                $debit = WalletTransaction::where('user_id', $user->id)
                    ->where('tx_ref', $txRef)
                    ->where('direction', 'debit')
                    ->first();
                if (!$debit || round((float) $debit->amount, 2) !== $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No matching wallet debit to reverse.',
                    ], 400);
                }
                $user->wallet_balance = round((float) $user->wallet_balance + $amount, 2);
            } else {
                if ((float) $user->wallet_balance < $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance.',
                    ], 400);
                }
                $user->wallet_balance = round((float) $user->wallet_balance - $amount, 2);
            }

            $user->save();
            WalletTransaction::create([
                'user_id' => $user->id,
                'tx_ref' => $txRef,
                'direction' => $direction,
                'amount' => $amount,
            ]);

            return response()->json([
                'success' => true,
                'data' => $this->payload($user, $amount, $txRef),
            ]);
        });
    }

    private function payload(User $user, float $moved, string $txRef): array
    {
        return [
            'amount' => (float) $user->wallet_balance,
            'moved' => $moved,
            'tx_ref' => $txRef,
            'currency' => 'NGN',
        ];
    }
}
