<?php

namespace App\application\guide;

use App\domain\guide\GuideRepository;
use App\domain\section\SectionRepository;
use Exception;

final class GuideUseCase
{
	public function __construct(private readonly GuideRepository $guideRepository, private readonly SectionRepository $sectionRepository) {}

	public function getAllGuides()
	{
		$guides = $this->guideRepository->findAll();
		return ['guides' => $guides];
	}

	public function findGuide(string $guideId)
	{
		$guide = $this->guideRepository->findGuideWithQualification($guideId);
		if (!$guide) {
			return new Exception('La guia que buscar no existe o no es valida', 404);
		}
		return ['guide' => $guide];
	}

	public function createGuide(mixed $body)
	{
		$name = $this->validateName($body->name);
		if ($name instanceof Exception) {
			return $name;
		}

		$guide = $this->guideRepository->createAndSetQualification((object) [...(array) $body, 'name' => $name]);

		$sections = $this->sectionRepository->validateSectionsId($body->sections);

		if (count($sections) !== count($body->sections)) {
			$this->guideRepository->deleteGuide($guide->id);
			return new Exception(
				'Parece que hubo un error al asignar las secciones por favor verifica que las secciones no correspondan a otra guia',
				400
			);
		}

		$this->sectionRepository->attachGuide($guide->id, $body->sections);
		return ['message' => 'EL cuestionario se creo correctamente', 'guide' => $guide];
	}

	public function disableGuide(string $guideId)
	{
		$guide = $this->guideRepository->findOne($guideId);
		if (!$guide) {
			return new Exception('El cuestionario ya ha sidio desactivado', 400);
		}
		$this->guideRepository->disableGuide($guide);
	}

	private function validateName(string $name): Exception|string
	{
		$name  = trim(mb_strtoupper($name));
		$guide = $this->guideRepository->findByName($name);
		return $guide ? new Exception('Ya existe un cuestionario con ese nombre', 400) : $name;
	}

	public function showGuideBySurvey(string $surveyId, string $guideId)
	{
		$guide = $this->guideRepository->findGuideBySurvey($surveyId, $guideId);
		if (!$guide) {
			return new Exception('La guia que buscar no existe o no es valida', 404);
		}
		return ['guide' => $guide];
	}

	public function searchGuidesByTypeAndName(string $type, string $name)
	{
		$type = trim(mb_strtolower($type));
		$name = trim(mb_strtoupper($name));

		return ['guides' => $this->guideRepository->findGuideByTypeAndName($type, $name)];
	}

	public function findAndDisableGudie(string $guideId)
	{
		$guide = $this->guideRepository->findOne($guideId);
		if (!$guide) {
			return new Exception('El cuestionario que intentas desactivar no exite', 400);
		}
		if (!$guide->status) {
			return new Exception('El cuestionario ya ha sido desactivado', 400);
		}
		$guide = $this->guideRepository->disableGuide($guide);
		return !$guide ? new Exception('Parece que hubo un error al desactivar el cuestionario', 500) :
		['message' => 'El cuestionario se desactivo correctamente', 'success' => true];
	}

	public function findAndEnableGudie(string $guideId)
	{
		$guide = $this->guideRepository->findOne($guideId);
		if (!$guide) {
			return new Exception('El cuestionario que intentas desactivar no exite', 400);
		}
		if ($guide->status) {
			return new Exception('El cuestionario ya ha sido activado', 400);
		}
		$guide = $this->guideRepository->enableGuide($guide);
		return !$guide ? new Exception('Parece que hubo un error al activar el cuestionario', 500) :
		['message' => 'El cuestionario se activo correctamente', 'success' => true];
	}

	public function findGuideDetail(string $guideId)
	{
		$guide = $this->guideRepository->findGuideDetail($guideId);
		if (!$guide) {
			return new Exception('La guia que buscar no existe o no es valida', 404);
		}
		return ['guide' => $guide];
	}
}
