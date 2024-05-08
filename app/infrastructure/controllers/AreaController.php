<?php
/**
 * Los controladores son la parte que procesa la información y solo deberia de enviar y recibir información, sin generar logica del negocio dentro de las clases para que no dependa la lógica de la interación con la entrada y salida de datos(infraestructura).
 */
namespace App\infrastructure\controllers;

use App\application\area\AreaUseCase;
use App\kernel\controllers\Controller;

final class AreaController extends Controller
{
	public function __construct(private readonly AreaUseCase $areaUseCase) {}

	public function getAreas()
	{
		$this->response($this->areaUseCase->findAllAreas());
	}

	public function getAreaDetail(string $areaId)
	{
		$this->response(($this->areaUseCase->getAreaDetailsById($areaId)));
	}
}
