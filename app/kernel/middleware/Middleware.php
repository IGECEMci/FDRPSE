<?php

/**
 * 	La clase middleware es una clase que implementa ciertas funciones y hereda métodos de la clase request
 * 
 */

namespace App\kernel\middleware;

use App\kernel\authentication\Auth;
use App\kernel\request\Request;

abstract class Middleware extends Request implements HttpMiddleware
{
	use Auth;
}
