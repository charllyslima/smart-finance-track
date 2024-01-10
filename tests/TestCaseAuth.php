<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestCaseAuth extends TestCase
{
    use CreatesApplication;

    /**
     * Cria um usuário e faz login com ele.
     *
     * @return array ['user' => User, 'token' => string]
     */
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
}
