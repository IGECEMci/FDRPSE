<?php
/**
 * 	La clase es responsable de conectar la base de datos, implementando eloquent para ahorrar tiempo en la configuración y consulta a la base de datos,
 * 	además de implementar un patron para solamente generar una instancia de la conneción independientemente de la veces que se instancie la clase.
 * 
 */
namespace App\infrastructure\database;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;

final class Database
{
	private static $instance;
	private Capsule $capsule;

	public function __construct()
	{
		$this->capsule = new Capsule();
	}

	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public function connection(): void
	{
		try {
			$this->capsule->addConnection([
				'driver'    => getenv('USER_DBDRIVER'),
				'host'      => getenv('USER_HOST'),
				'database'  => getenv('DATABASE'),
				'username'  => getenv('USER_USERNAME'),
				'password'  => getenv('USER_PASSWORD'),
				'charset'   => 'utf8',
				'collation' => '',
				'prefix'    => '',
				'schema'    => 'public',
				'sslmode'   => 'prefer',
			]);

			$this->capsule->setAsGlobal();
			$this->capsule->bootEloquent();

			$this->addConnection();
		} catch (Exception $e) {
			new Exception($e->getMessage(), 500);
		}
	}

	private function addConnection()
	{
		$this->capsule->addConnection([
			'driver'    => getenv('USER_DBDRIVER'),
			'host'      => getenv('USER_HOST'),
			'database'  => getenv('USER_DATABASE'),
			'username'  => getenv('USER_USERNAME'),
			'password'  => getenv('USER_PASSWORD'),
			'charset'   => 'utf8',
			'collation' => '',
			'prefix'    => '',
			'schema'    => 'public',
			'sslmode'   => 'prefer',
		], 'user_db');

		$this->capsule->setAsGlobal();
		$this->capsule->bootEloquent();
	}
}
