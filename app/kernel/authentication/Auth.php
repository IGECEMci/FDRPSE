<?php

/**
 * La autencticación se genera a través de JWT con una duración de 2 horas por sesión de usuario hasta que sea generado, se utiliza un trait para poder 
 * utilizado en diferentes modulos.
 */


namespace App\kernel\authentication;

use App\domain\user\User;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

trait Auth
{
	public static function auth()
	{
		$session = $_SERVER['HTTP_SESSION'] ?? '';
		$key     = getenv('JWT_SECRET_KEY');
		$decode  = JWT::decode($session, new Key($key, 'HS256'));
		return $decode->user;
	}

	public static function createSession(User $payload)
	{
		$key  = getenv('JWT_SECRET_KEY');
		$user = static::parseUserData($payload);
		$data = [
			'exp' => strtotime('now') + 7200,
			'user' => $user,
		];
		$token = JWT::encode($data, $key, 'HS256');
		return ['user' => $user, 'session' => $token];
	}

	public static function check(string $token): ?stdClass
	{
		try {
			if (!isset($token)) {
				return null;
			}
			$key     = getenv('JWT_SECRET_KEY');
			$session = JWT::decode($token, new Key($key, 'HS256'));
			return $session ? $session->user : null;
		} catch (Exception $e) {
			return null;
		}
	}


	private static function parseUserData(User $user): mixed
	{
		['apellidoM' => $apellidoM, 'apellidoP' => $apellidoP, 'id' => $id, 'nombre' => $nombre, 'userName' => $userName, 'tipo' => $tipo] = $user;
		return ['apellidoM' => $apellidoM, 'apellidoP' => $apellidoP, 'id' => $id, 'nombre' => $nombre, 'userName' => $userName, 'tipo' => $tipo];
	}
}
