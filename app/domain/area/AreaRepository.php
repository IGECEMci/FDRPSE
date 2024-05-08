<?php

/**
 * Los repositorios son interfaces de como va a lucir una clase y que métodos debe implementar y son usados en la capa de aplicación 
 * para usar solo la firma y no la implementación
 */

namespace App\domain\area;

use App\domain\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

interface AreaRepository extends BaseRepository
{
	public function findAreas(): Collection;

	public function findAreaByIdAndGetChildAreas(string $areaId): Collection;
	public function countAreasByAreasId(array $areasId): int;
}
