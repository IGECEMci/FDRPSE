<?php

/**
 * Los modelos son la interpretación de como luce la base de datos y que interactua directamente con ella, en este caso al implementar eloquent
 * extiende de la clase Model para poder acceder a diferentes métodos.
 * 
 * Los modelos no deberían tener lógica dentro de ellos, unicamente métodos de relación con otros modelos.
 */

namespace App\domain\area;

use App\domain\user\User;
use Illuminate\Database\Eloquent\Model;

final class Area extends Model
{
	protected $table      = 'areas';
	protected $connection = 'user_db';

	protected $withCount = ['users'];

	public function users()
	{
		return $this->hasMany(User::class, 'id_area');
	}

	public function subdirections()
	{
		return $this->hasMany(self::class, 'area_padre');
	}

	public function departments()
	{
		return $this->hasMany(self::class, 'area_padre');
	}

	public function father()
	{
		return $this->belongsTo(self::class, 'area_padre');
	}
}
