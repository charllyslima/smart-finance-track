<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_correct_credentials()
    {
        // Criar um usuário de teste com senha forte
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('ValidPass123!'), // Senha forte
        ]);

        // Tentar fazer login com as credenciais corretas
        $response = $this->post('/api/login', [
            'email' => 'test@example.com',
            'password' => 'ValidPass123!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token', 'token_type', 'user'
        ]);
    }

    /** @test */
    public function user_cannot_login_with_incorrect_password()
    {
        // Criar um usuário de teste com senha forte
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('ValidPass123!'), // Senha forte
        ]);

        // Tentar fazer login com uma senha incorreta
        $response = $this->post('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword123!',
        ]);

        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function user_cannot_login_with_incorrect_email()
    {
        // Criar um usuário de teste
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('ValidPass123!'),
        ]);

        // Tentar fazer login com um e-mail incorreto
        $response = $this->post('/api/login', [
            'email' => 'wrongemail@example.com',
            'password' => 'ValidPass123!',
        ]);

        $response->assertStatus(401); // Unauthorized
    }

}
