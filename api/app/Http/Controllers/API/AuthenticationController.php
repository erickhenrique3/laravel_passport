<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
	/**
	 * @method Registra um novo usuário.
	 * @param Request $request Dados do usuário para o registro.
	 * @return JsonResponse Resposta JSON com o status da operação e dados do usuário.
	 */
	public function register(Request $request): JsonResponse
	{
		$validatedData = $request->validate([
			"name" => "required|string",
			"email" => "required|string|email|unique:users",
			"password" => "required|confirmed" 
		]);

		User::create([
			"name" => $request->name,
			"email" => $request->email,
			"password" => bcrypt($request->password) 
		]);

		return response()->json([
			"status" => true,
			"message" => "User registered successfully",
			"data" =>  $validatedData
		]);
	}

	/**
	 * @method Faz login do usuário e retorna um token de autenticação.
	 * @param Request $request Dados de login do usuário.
	 * @return JsonResponse Resposta JSON com o status da operação e token de autenticação.
	 */
	public function login(Request $request): JsonResponse
	{
		$request->validate([
			"email" => "required|email",
			"password" => "required"
		]);

		$user = User::where("email", $request->email)->first();

		if (!empty($user)) {
			if (Hash::check($request->password, $user->password)) {
				$token = $user->createToken("myToken")->accessToken;
				return response()->json([
					"status" => true,
					"message" => "Login sucessfull",
					"token" => $token
				]);
			} else {
				return response()->json([
					"status" => false,
					"message" => "Password din'd Match"
				]);
			}
		} else {
			return response()->json([
				"status" => false,
				"message" => "Invalid email value",
			]);
		}
	}

	/**
	 * @method Retorna as informações do perfil do usuário autenticado.
	 * @return JsonResponse Resposta JSON com o status da operação e dados do perfil do usuário.
	 */
	public function profile(): JsonResponse {
		$userData = Auth::user();
		return response()->json([
			"status" => true,
			"message" => "profile information",
			"data" => $userData
		]);
	}
    
	/**
	 * @method Faz logout do usuário e revoga o token de autenticação.
	 * @param Request $request Dados da solicitação de logout.
	 * @return JsonResponse Resposta JSON com o status da operação.
	 */
	public function logout(Request $request): JsonResponse {
		$request->user()->token()->revoke();

    return response()->json([
        "status" => true,
        "message" => "Usuário deslogado com sucesso"
    ]);
	}
}