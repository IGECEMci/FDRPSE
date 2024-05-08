<?php
/**
 * Las clases request sirven para validar las peticiones que reciben datos del cliente, es una clase que extiende de la clase Request para poder acceder a los métodos de la clase.
 */
namespace App\infrastructure\requests\authentication;

use App\kernel\request\HttpRulesRequest;
use App\kernel\request\Request;

final class LoginRequest extends Request implements HttpRulesRequest
{
	public static function rules(): array
	{
		return [
			'username' => 'required',
			'password' => 'required|min:4',
		];
	}

	public static function messages(): array
	{
		return [
			'username:required' => 'El nombre de usuario es requerido',
			'password:required' => 'La contraseña es requerida',
			'password:min'      => 'La contraseña debe contener al menos 8 caracteres',
		];
	}
}
