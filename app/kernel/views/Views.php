<?php

/**
 *
 * El trair Views es encapsula la logica para poder retornar vistas de lado del servidor ademas de generar vistas en forma de buffer para poder
 * generar reportes pdfs o cuando sea necesario (procesa e interpreta el archivo con las funciones o variables del archivo y lo retorna en un string)
 * 
 */
namespace App\kernel\views;

trait Views
{
	public function view(string $name, $data = [])
	{
		if (!empty($data)) {
			$data = json_decode(json_encode($data));
		}
		require_once __DIR__ . "/../../infrastructure/views/{$name}.php";
	}

	public function renderBufferView(string $name, mixed $data = []): string
	{
		ob_start();
		if (!empty($data)) {
			$data = json_decode(json_encode($data));
		}
		require_once __DIR__ . "/../../infrastructure/views/{$name}.php";
		return ob_get_clean();
	}
}
