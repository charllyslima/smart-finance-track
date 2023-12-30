<?php

namespace FinancialTransactions;

use App\Models\FinancialTransaction;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ListTest extends TestCase
{
    use RefreshDatabase;

    protected function createUserAndLogin(): array
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('ValidPass123!'),
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'ValidPass123!', // Use a mesma senha que foi definida acima
        ]);

        $loginResponse->assertStatus(200);

        $accessToken = $loginResponse->json('access_token');

        return ['user' => $user, 'token' => $accessToken];
    }

    public function testReturnsCorrectTotals(): void
    {
        $loginData = $this->createUserAndLogin();

        $user = $loginData['user'];
        $accessToken = $loginData['token'];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        FinancialTransaction::factory()->create([
            'user_id' => $user->id,
            'transaction_type' => FinancialTransaction::DEPOSIT,
            'amount' => 100.00,
        ]);

        FinancialTransaction::factory()->create([
            'user_id' => $user->id,
            'transaction_type' => FinancialTransaction::WITHDRAWAL,
            'amount' => 50.00,
        ]);

        $response = $this->get('/api/financial-transactions');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'financialTransactions',
            'totalDeposit',
            'totalWithdraw',
            'totalBalance',
            'totalInvestment',
        ]);

        $response->assertJson([
            'totalDeposit' => 100,
            'totalWithdraw' => 50,
            'totalBalance' => 50,
            'totalInvestment' => 0,
        ]);
    }

    public function testPagination(): void
    {
        $loginData = $this->createUserAndLogin();

        $user = $loginData['user'];
        $accessToken = $loginData['token'];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        FinancialTransaction::factory()->count(15)->create(['user_id' => $user->id]);

        $response = $this->get('/api/financial-transactions?page=2&page_size=10');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'financialTransactions.data');
    }

}
