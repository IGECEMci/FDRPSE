<?php


/**
 * La clase controller es una calse de la cual no se pueden generar instancias pero sirver para enviar, recibir información del cliente
 * Además de poder usar las funciones del trait de autenticación, retornar vistas desde el servidor o generar reportes de Excel.
 * 
 * Además extiende dee la clase request para poder acceder a los headers, al body, a los query params o para validar las request.
 * 
 */

namespace App\kernel\controllers;

use App\kernel\authentication\Auth;

use App\kernel\request\Request;
use App\kernel\views\Views;
use App\shared\traits\ExcelTrait;
use Exception;

abstract class Controller extends Request
{
	use Auth, Views, ExcelTrait;

	protected function response(mixed $response, int $statusCode = 200)
	{
		if ($response instanceof Exception) {
			return $this->responseJson(['message' => $response->getMessage()], $response->getCode() ?? 400);
		}
		$this->responseJson($response, $statusCode);
	}
}
