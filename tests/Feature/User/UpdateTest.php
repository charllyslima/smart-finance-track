<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCaseAuth;

class UpdateTest extends TestCaseAuth
{
    use RefreshDatabase, WithFaker;

    /**
     * Testa a atualização de informações do usuário com sucesso.
     *
     * @return void
     */
    public function testUpdateUserSuccessfully(): void
    {

        $loginData = $this->createUserAndLogin();

        $user = $loginData['user'];
        $accessToken = $loginData['token'];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ]);


        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];


        $response = $this->put('/api/update/profile', $data);

        $response->assertStatus(200);

        $response->assertJson(['message' => 'Dados atualizados com sucesso!']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    /**
     * Testa a falha na atualização de informações do usuário quando o campo "name" não está presente.
     *
     * @return void
     */
    public function testUpdateUserFailedNameRequired(): void
    {
        $loginData = $this->createUserAndLogin();
        $accessToken = $loginData['token'];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        $data = [
            'email' => $this->faker->unique()->safeEmail,
        ];

        $response = $this->put('/api/update/profile', $data);

        $response->assertStatus(422);

        $response->assertJson(['message' => 'Os dados fornecidos são inválidos.']);
        $response->assertJson([
            'errors' => [
                'name' => ['O campo nome é obrigatório.'],
            ],
        ]);
    }

    /**
     * Testa a falha na atualização de informações do usuário quando o campo "name" contém caracteres inválidos.
     *
     * @return void
     */
    public function testUpdateUserFailedNameInvalidCharacters(): void
    {
        $loginData = $this->createUserAndLogin();
        $accessToken = $loginData['token'];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        $data = [
            'name' => 'John-Due 3',
            'email' => $this->faker->unique()->safeEmail,
        ];

        $response = $this->put('/api/update/profile', $data);

        $response->assertStatus(422);

        $response->assertJson(['message' => 'Os dados fornecidos são inválidos.']);
        $response->assertJson([
            'errors' => [
                'name' => ['O campo nome contém caracteres inválidos. Permitidos: letras, espaços, hifens, pontos e aspas simples.'],
            ],
        ]);
    }

    /**
     * Testa a falha na atualização de informações do usuário quando o campo "email" não está presente.
     *
     * @return void
     */
    public function testUpdateUserFailedEmailRequired(): void
    {
        $loginData = $this->createUserAndLogin();
        $accessToken = $loginData['token'];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->put('/api/update/profile', $data);

        $response->assertStatus(422);

        $response->assertJson(['message' => 'Os dados fornecidos são inválidos.']);
        $response->assertJson([
            'errors' => [
                'email' => ['O campo e-mail é obrigatório.'],
            ],
        ]);
    }

    /**
     * Testa a falha na atualização de informações do usuário quando o campo "email" não é um endereço de e-mail válido.
     *
     * @return void
     */
    public function testUpdateUserFailedEmailInvalidFormat(): void
    {
        $loginData = $this->createUserAndLogin();
        $accessToken = $loginData['token'];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        $data = [
            'name' => $this->faker->name,
            'email' => 'email_invalido',
        ];

        $response = $this->put('/api/update/profile', $data);

        $response->assertStatus(422);

        $response->assertJson(['message' => 'Os dados fornecidos são inválidos.']);
        $response->assertJson([
            'errors' => [
                'email' => ['O campo e-mail deve ser um endereço de e-mail válido.'],
            ],
        ]);
    }

    /**
     * Testa a falha na atualização de informações do usuário quando o campo "email" já está em uso.
     *
     * @return void
     */
    public function testUpdateUserFailedEmailUnique(): void
    {
        $loginData1 = $this->createUserAndLogin();
        $accessToken1 = $loginData1['token'];

        $user2 = User::factory()->create();

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken1,
        ]);

        $data = [
            'name' => $this->faker->name,
            'email' => $user2->email,
        ];

        $response = $this->put('/api/update/profile', $data);

        $response->assertStatus(422);

        $response->assertJson(['message' => 'Os dados fornecidos são inválidos.']);
        $response->assertJson([
            'errors' => [
                'email' => ['O e-mail já está em uso.'],
            ],
        ]);
    }

    /**
     * Testa a atualização de informações do usuário quando ele não está logado.
     *
     * @return void
     */
    public function testUpdateUserNotLoggedIn(): void
    {

        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];

        $response = $this->put('/api/update/profile', $data);

        $response->assertStatus(401);

        $response->assertJson(['message' => 'Usuário não autenticado.']);
    }

    /**
     * Testa a atualização de informações do usuário quando o nome excede o limite de caracteres permitido.
     *
     * @return void
     */
    public function testUpdateUserNameTooLong(): void
    {
        $loginData = $this->createUserAndLogin();

        $user = $loginData['user'];
        $accessToken = $loginData['token'];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ]);

        $data = [
            'name' => str_repeat('A', 256),
            'email' => $this->faker->unique()->safeEmail,
        ];

        $response = $this->put('/api/update/profile', $data);

        $response->assertStatus(422);

        $response->assertJson(['message' => 'Os dados fornecidos são inválidos.']);
        $response->assertJsonValidationErrors(['name']);
    }

}
