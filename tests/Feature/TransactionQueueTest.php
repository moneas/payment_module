<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Jobs\ProcessTransaction;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_processes_transaction_via_queue()
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/transactions', [
            'user_id' => $user->id,
            'amount' => 100.00,
        ]);

        $response->assertStatus(201);

        Queue::assertPushed(ProcessTransaction::class, function ($job) use ($user) {
            return $job->transaction->user_id === $user->id;
        });
    }

}
