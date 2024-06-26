<?php


namespace App\application\survey;

use App\domain\area\AreaRepository;
use App\domain\guide\GuideRepository;
use App\domain\guideSurvey\GuideStatus;
use App\domain\question\Question;
use App\domain\user\UserRepository;
use Exception;

final class SurveyUseCase
{
	public function __construct(
		private readonly SurveyService $surveyService,
		private readonly UserRepository $userRepository,
		private readonly AreaRepository $areaRepository,
		private readonly GuideRepository $guideRepository,
	) {
	}

	public function getAllSurveys(int $page)
	{
		return $this->surveyService->getSurvys($page);
	}

	public function getSurveyById(string $surveyId)
	{
		return ['survey' => $this->surveyService->getSurveyDetail($surveyId)];
	}

	public function startNewSurvey(array $guides)
	{
		$survey = $this->surveyService->startSurvey();
		if ($survey instanceof Exception) {
			return $survey;
		}

		$areValidIds = $this->guideRepository->countGuidesById($guides);
		if (count($guides) !== count($areValidIds)) {
			return new Exception('Los cuestionarios no son validos', 400);
		}

		$guides = [];
		foreach ($areValidIds as $key => $guide) {
			$guides[$guide['id']] = ['qualification' => $guide['qualification'], 'status' => 1];
		}

		return [
			'survey'  => $this->surveyService->attachGuidesToSurvey($survey, $guides),
			'message' => 'Se ha generado una serie de cuestionarios',
		];
	}

	public function saveAnswers(array $body, string $type, string $surveyId, string $guideId)
	{
		if ($type === Question::NONGRADABLE) {
			return $this->surveyService->saveNongradableAnswersByUser($body, $surveyId, $guideId);
		}
		return $this->surveyService->saveAnswersByUser($body,  $surveyId, $guideId);
	}

	public function getQuestionsByUser()
	{
		return $this->surveyService->getQuestionInsideSection();
	}

	public function startSurveyByUser(string $surveyId, string $guideId)
	{
		return $this->surveyService->setSurveyToUser($surveyId, $guideId);
	}

	public function finalizeSurveyByUser(string $surveyId, string $guideId)
	{
		return $this->surveyService->finalzeUserSurvey($surveyId, $guideId);
	}

	public function getInProgressSurvey()
	{
		return $this->surveyService->existSurveyInProgress();
	}

	public function getGuideDetail(string $guideId)
	{
		return [
			'guide' => $this->guideRepository->findOne($guideId),
		];
	}

	public function findSurveyByName(string $surveyId, string $guideId, string $name, string $areaId, string $subareaId)
	{
		return [
			'survey' => $this->surveyService->findSurveyByNameAndAreas($surveyId, $guideId, $name, $areaId, $subareaId),
		];
	}

	public function findUserDetails(string $surveyId, string $userId, string $guideId)
	{
		$surveyUser = $this->surveyService->getDetailsByUser($surveyId, $userId, $guideId);
		if ($surveyUser instanceof Exception) {
			return $surveyUser;
		}
		return ['guide_user' => $surveyUser];
	}

	public function getUserWithoutSurvey()
	{
		$users = $this->userRepository->countTotalAvailableUsers();
		return ['users' => $users];
	}

	public function finalizeSurvey(string $surveyId)
	{
		return $this->surveyService->finalizeSurvey($surveyId);
	}

	public function tryToFinalizeGuide(string $surveyId, string $guideId)
	{
		$totalUsers = $this->userRepository->countTotalAvailableUsers();
		return $this->surveyService->finalizedGuideInsideSurvey($surveyId, $guideId, $totalUsers);
	}


	// private function calculateUsersHaveToAnswers(int $usersCount): int
	// {
	// 	$totalUsers = 0.9604 * $usersCount;
	// 	$totalConst = 0.0025 * ($usersCount - 1) + 0.9604;
	// 	return round($totalUsers / $totalConst);
	// }

	public function getDataToGenerateSurveyUserResume(string $userId)
	{
		$surveyUser = $this->surveyService->getLastSurveyByUser($userId);
		return !$surveyUser ? new Exception('El cuestionario no esta disponible', 404) : $surveyUser;
	}

	public function changeGuideStatusToPaused(string $surveyId, string $guideId, int $status)
	{
		$guideSurvey = $this->guideRepository->findGuideBySurvey($surveyId, $guideId);
		if (!$guideSurvey) {
			return new Exception('El cuestionario no existe o no es valido', 404);
		}

		$guide = '';

		if ($status === GuideStatus::PAUSED->value && $guideSurvey->surveys[0]->pivot->status === GuideStatus::INPROGRESS->value) {
			// if ($this->surveyService->canContinueGuide($surveyId, $guideId) > 0 && $this->surveyService->existGuideInProgress($surveyId) > 0) {
			// 	return new Exception('El cuestionario no puede ser pausado porque existen usuarios respondiendo el cuestionario', 400);
			// }
			$guide = $this->guideRepository->changeGuideSurveyStatus($guideSurvey, $surveyId, GuideStatus::PAUSED);
			return ['guide' => $guide];
		}
		if ($status === GuideStatus::INPROGRESS->value && $guideSurvey->surveys[0]->pivot->status === GuideStatus::PAUSED->value) {
			// if ($this->surveyService->canContinueGuide($surveyId, $guideId) > 0 && $this->surveyService->existGuideInProgress($surveyId) > 0) {
			// 	return new Exception('El cuestionario no puede ser pausado porque existen usuarios respondiendo el cuestionario', 400);
			// }
			$guide = $this->guideRepository->changeGuideSurveyStatus($guideSurvey, $surveyId, GuideStatus::INPROGRESS);
			return ['guide' => $guide];
		}

		return new Exception(
			'Parece que hubo un error al ' . ($status === GuideStatus::PAUSED->value ? 'pausar' : 'continuar') . ' la guía',
			400
		);
	}

	public function hasProgresGuide(string $surveyId, string $guideId)
	{
		return ['guide_count' => $this->surveyService->getInProgressGuideSurvey($surveyId, $guideId)];
	}

	public function startGuideInsideSurvey(string $surveyId, string $guideId)
	{
		return $this->surveyService->startGuideAndPauseOthersGuides($surveyId, $guideId);
	}

	public function getSurveyDetailByArea(string $surveyId, string $guideId, string $areaId)
	{
		return $this->surveyService->getSurveyGuideByArea($surveyId, $guideId, $areaId);
	}
}
