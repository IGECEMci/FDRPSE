<?php

/**
 * Las rutas son encargadas de procesar una ruta y de implementar un método de un controlador.
 */

namespace App\infrastructure\routes\area;

use App\application\area\AreaUseCase;

use App\domain\area\Area;
use App\infrastructure\controllers\AreaController;
use App\infrastructure\repositories\area\AreaRepository;
use Bramus\Router\Router;

function router(Router $router)
{
	$areaRepository = new AreaRepository(new Area());
	$areaUseCase    = new AreaUseCase($areaRepository);
	$areaController = new AreaController($areaUseCase);

	$router->get('/', function () use ($areaController) {
		$areaController->getAreas();
	});

	$router->get('/detail/{id}', function (string $id) use ($areaController) {
		$areaController->getAreaDetail($id);
	});
}
