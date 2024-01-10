<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseAuth;

class DeleteTest extends TestCaseAuth
{
    use RefreshDatabase;

    /**
     * Testa a exclusão do usuário autenticado.
     *
     * @return void
     */
    public function testDeleteAuthenticatedUser(): void
    {

        $loginData = $this->createUserAndLogin();

        $user = $loginData['user'];
        $accessToken = $loginData['token'];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        $response = $this->delete('/api/delete/profile');

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Testa a exclusão do usuário não autenticado.
     *
     * @return void
     */
    public function testDeleteUnauthenticatedUser(): void
    {
        $response = $this->delete('/api/delete/profile');

        $response->assertStatus(401);
    }
}
