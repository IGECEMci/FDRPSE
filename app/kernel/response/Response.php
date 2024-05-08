<?php

/**
 * La clase Response es la responsable de retornar información al cliente en formato Json o de descarga.
 */
namespace App\kernel\response;

abstract class Response implements HttpResponse
{
	private static array $codes = [
		200 => '200 OK',
		201 => '201 Created',
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		422 => '422 Unprocessable Entity',
		500 => '500 Internal Server Error',
	];

	public static function responseJson($data = [], int $status = 200, array $headers = []): void
	{
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header_remove('X-Powered-By');
		header('Pragma: no-cache');
		http_response_code($status);
		header('Status:' . self::$codes[$status]);
		echo json_encode($data);
		exit();
	}

	public static function responseDownload($file, int $status = 200, array $headers = []): void
	{
		header('Access-Control-Allow-Origin: *');
		header('Cache-Control: no-transform,public,max-age=300,s-maxage=900');
		header('Content-Type: application/octet-stream');
		header('Expires: 0');
		header('Pragma: public');

		http_response_code($status);
		header('Status:' . self::$codes[$status]);
	
		exit();
	}
}
