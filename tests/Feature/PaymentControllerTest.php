<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_transaction_via_api()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/transactions', [
            'user_id' => $user->id,
            'amount' => 100.00,
        ]);

        $response->assertStatus(201); // Assuming 201 Created on success
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 100.00,
            'status' => 'pending',
        ]);
    }

    public function test_can_get_user_transactions_via_api()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        Transaction::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/' . $user->id . '/transactions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'amount',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

}
