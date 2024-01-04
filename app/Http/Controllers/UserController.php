<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * Remove o próprio usuário autenticado.
     *
     * @OA\Delete(
     *     path="/api/delete/profile",
     *     summary="Exclui o próprio usuário autenticado",
     *     description="Exclui permanentemente o usuário autenticado do sistema.",
     *     operationId="destroy",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=204,
     *         description="Registro de usuário excluído com sucesso",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Falha ao excluir o registro do usuário",
     *     ),
     * )
     *
     * @return JsonResponse
     */

    public function destroy(): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user) {
                $user->delete();
                return response()->json(null, 204);
            }

            return response()->json(['message' => 'Usuário não encontrado'], 404); // Se o usuário não for encontrado, retorna 404.
        } catch (\Exception $e) {
            return response()->json(['message' => 'Falha ao excluir o registro do usuário'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/update/profile",
     *     summary="Atualizar informações do usuário autenticado",
     *     description="Atualiza as informações do usuário autenticado.",
     *     operationId="update",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do usuário a serem atualizados",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Novo Nome"),
     *             @OA\Property(property="email", type="string", format="email", example="novonome@example.com"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados atualizados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dados atualizados com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Não autorizado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário não encontrado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Falha ao atualizar dados",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Falha ao atualizar dados, tente novamente.")
     *         )
     *     )
     * )
     *
     * @param UserUpdateRequest $request
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request): JsonResponse
    {
        $user = User::findOrFail(Auth::id());
        $user->update($request->validated());
        return response()->json(['message' => 'Dados atualizados com sucesso!'], 200);
    }
}
