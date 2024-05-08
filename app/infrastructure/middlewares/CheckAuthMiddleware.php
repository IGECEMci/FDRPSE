<?php
/**
 * Los middlewares son los intermediarios entre las rutas y los controladores y son escenciales para filtar información o usuarios
 * y en este caso el middleware verifica que el usuario esta autenticado.
 */

namespace App\infrastructure\middlewares;

use App\kernel\middleware\Middleware;

final class CheckAuthMiddleware extends Middleware
{
	public function handle(): void
	{
		if (!$this->check($_SERVER['HTTP_SESSION'] ?? '')) {
			$this->responseJson(['message' => 'La sesión caducó'], 401);
			exit();
		}
	}
}
