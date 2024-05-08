<?php

/**
 * Los casos de uso son los encargado de manejar la lógica de negocio e implementan la firma de las funciones de la capa de dominio, es decir, 
 * en vez de implementar la funciones con su lógica que interactuan con la base de datos implementa la firma de las funciones de ese modo se genera
 * una abstracción porque aunque interactuan directamente, la lógica de negocio no depende del dominio.
 * 
*/

namespace App\application\authentication;

use App\domain\user\UserRepository;
use Exception;

final class AuthenticationUseCase
{
	public function __construct(private readonly UserRepository $userRepository) {}

	public function signin(string $username, string $password)
	{
		$user = $this->userRepository->findByUsername($username);
		if (!$user) {
			return new Exception('El usuario o contraseña no es valido', 400);
		}
		return md5($password) === $user->contrasenia ? $user : new Exception('El usuario o contraseña no es valido', 400);
	}

	public function checkUserSession(mixed $user)
	{
		if (!$user) {
			return new Exception('Unauthorized', 401);
		}
		$user = $this->userRepository->findOne((string) $user->id);
		return $user ? $user : new Exception('Unauthorized', 401);
	}
}
