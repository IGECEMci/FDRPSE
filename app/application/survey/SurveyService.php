<?php


namespace App\application\survey;

use App\domain\category\Category;
use App\domain\domain\Domain;
use App\domain\guideSurvey\GuideStatus;
use App\domain\guideSurvey\GuideSurveyRepository;
use App\domain\guideUser\GuideUser;
use App\domain\guideUser\GuideUserRepository;
use App\domain\guideUser\TypeQuestion;
use App\domain\qualificationQuestion\QualificationQuestionRepository;
use App\domain\question\Question;
use App\domain\question\QuestionRepository;
use App\domain\section\Section;

use App\domain\survey\Survey;
use App\domain\survey\SurveyRepository;
use App\kernel\authentication\Auth;
use Exception;

final class SurveyService
{
	use Auth;

	public function __construct(
		private readonly SurveyRepository $surveyRepository,
		private readonly GuideUserRepository $guideUserRepository,
		private readonly QuestionRepository $questionRepository,
		private readonly GuideSurveyRepository $guideSurveyRepository,
		private readonly QualificationQuestionRepository $qualificationQuestionRepository,
	) {
	}

	public function getSurvys(int $page)
	{
		return $this->surveyRepository->findAllSurveys($page);
	}

	public function getSurveyDetail(string $surveyId)
	{
		return $this->surveyRepository->findSurveyWithDetails($surveyId);
	}

	public function startSurvey()
	{
		if (!$this->surveyRepository->canStartNewSurvey()) {
			return new Exception('Hay un cuestionatio en progreso por lo que no se puede comenzar la nueva encuesta', 400);
		}
		return $this->surveyRepository->create(['start_date' => date('Y-m-d\TH:i:s.000'), 'status' => Survey::PENDING]);
	}

	public function saveNongradableAnswersByUser(array $body, string $surveyId, string $guideId)
	{
		$guideSurvey = $this->guideSurveyRepository->findByGuideSurvey($surveyId, $guideId);
		if (!$guideSurvey) {
			return new Exception('Parece que hubo un error al registar la respusta', 400);
		}

		if ($guideSurvey->status != GuideStatus::INPROGRESS->value) {
			return new Exception('La encuesta que intentas responder no esta disponible', 400);
		}

		$guideUser = $this->guideUserRepository->getCurrentGuideUser(
			$guideSurvey->guide_id,
			$this->auth()->id,
			$guideSurvey->survey_id
		);

		if (!$guideUser) {
			return new Exception('Parece que hubo un error al registar la respusta', 500);
		}

		$isValidRequest = array_map(function ($question) use ($guideSurvey) {
			$question = (object) $question;
			if ($question->type === TypeQuestion::SECTION->value) {
				$section = $this->guideSurveyRepository->findQuestionInsideGuide($guideSurvey, $question->question_id);
				return $section ? $this->validateSectionBinaryToSaveQuestion($section, $question->qualification) : false;
			}
			$questionDB = $this->questionRepository->findOne($question->question_id);
			return $questionDB ? $this->validateQuestions($questionDB, $question->qualification) : false;
		}, $body);


		if (in_array(false, $isValidRequest)) {
			return new Exception('Parece que hubo un error al guardar las preguntas', 400);
		}

		$guideUser = $this->guideUserRepository->getCurrentGuideUser(
			$guideSurvey->guide_id,
			$this->auth()->id,
			$guideSurvey->survey_id
		);

		if ($guideUser->answers !== '') {
			$body = $this->hasPreviousQuestion($guideUser->answers, $isValidRequest);
		} else {
			$body = $isValidRequest;
		}

		$this->guideUserRepository->saveAnswer($guideUser, $body);
		return ['message' => 'La preguntas se guardaron correctmente', 'guide' => $guideUser];
	}

	public function saveAnswersByUser(array $body, string $surveyId, string $guideId)
	{
		$guideSurvey = $this->guideSurveyRepository->findByGuideSurvey($surveyId, $guideId);
		if (!$guideSurvey) {
			return new Exception('Parece que hubo un error al registar la respusta', 400);
		}

		if ($guideSurvey->status != GuideStatus::INPROGRESS->value) {
			return new Exception('La encuesta que intentas responder no esta disponible', 400);
		}

		$guideUser = $this->guideUserRepository->getCurrentGuideUser(
			$guideSurvey->guide_id,
			$this->auth()->id,
			$guideSurvey->survey_id
		);

		if (!$guideUser) {
			return new Exception('Parece que hubo un error al registar la respusta', 500);
		}

		$isValidRequest = array_map(function ($question) use ($guideSurvey) {
			$question = (object) $question;
			if ($question->type === TypeQuestion::SECTION->value) {
				$section = $this->guideSurveyRepository->findQuestionInsideGuide($guideSurvey, $question->question_id);
				return $section ? $this->validateSectionBinaryToSaveQuestion($section, $question->qualification) : false;
			}
			$questionDB = $this->questionRepository->findOne($question->question_id);
			return $questionDB ? $this->validateQuestions($questionDB, $question->qualification) : false;
		}, $body);


		if (in_array(false, $isValidRequest)) {
			return new Exception('Las preguntas que intentas guardar no exiten', 400);
		}

		$guideUser = $this->guideUserRepository->getCurrentGuideUser(
			$guideUser->guide_id,
			$this->auth()->id,
			$guideSurvey->survey_id
		);

		if ($guideUser->answers !== '') {
			$body = $this->hasPreviousQuestion($guideUser->answers, $isValidRequest);
		} else {
			$body = $isValidRequest;
		}

		$this->guideUserRepository->saveAnswer($guideUser, $body);
		return ['message' => 'La preguntas se guardaron correctmente'];
	}

	public function getQuestionInsideSection()
	{
		return $this->questionRepository->getQuestionBySection();
	}

	public function finalizeSurvey(string $surveyId)
	{
		$survey = $this->surveyRepository->findSurveyWithDetails($surveyId);
		if (!$survey) {
			return new Exception('La series de cuestionarios que intentas finalizar no existe', 404);
		}
		if ($survey->status) {
			return new Exception('La series de cuestionarios ya han sido finalizados', 404);
		}

		$canFinishGuide = array_filter(
			(array) [...$survey->guides],
			fn ($guide) => $guide->pivot->status === GuideStatus::FINISHED->value
		);
		if (count($canFinishGuide) !== count($survey->guides)) {
			return new Exception('La series de cuestionarios no se puede finalizar porque hay guías en proceso', 404);
		}

		$survey = $this->surveyRepository->endSurvey($survey);
		return ['survey' => $survey, 'message' => 'La serie de cuestionarios finalizo correctamente'];
	}

	public function setSurveyToUser(string $surveyId, string $guideId)
	{
		$survey = $this->surveyRepository->getCurrentSurvey();
		if (!$survey) {
			return new Exception('No hay encuestas disponibles', 404);
		}

		$guide = $this->guideSurveyRepository->findByGuideSurvey($surveyId, $guideId);

		if (!$guide || $guide->status !== GuideStatus::INPROGRESS->value) {
			return new Exception('El cuestionario que intentas responder no esta disponible', 404);
		}

		$guideUser = $this->guideUserRepository->getCurrentGuideUser($guide->guide_id, $this->auth()->id, $guide->survey_id);

		if (gettype($guideUser->answers) === 'array' && count($guideUser->answers) > 0) {
			$guideUser = $this->guideUserRepository->clearOldAnswers($guideUser);
		}

		return ['survey' => $guideUser, 'success' => true];
	}

	public function finalzeUserSurvey(string $surveyId, string $guideId)
	{
		$survey = $this->surveyRepository->getCurrentSurvey();
		if (!$survey) {
			return new Exception('La encuesta que intentas guardar no existe', 404);
		}
		$guide = $this->guideSurveyRepository->findByGuideSurvey($surveyId, $guideId);
		if (!$guide) {
			return new Exception('La encuesta que intentas guardar no existe', 404);
		}

		$userQualification = $this->calculateUserQualification(
			$this->guideUserRepository->getCurrentGuideUser($guideId, $this->auth()->id, $surveyId)
		);

		$surveyUser = $this->guideUserRepository->finalizeGuideUser(
			$guide->survey_id,
			$guide->guide_id,
			$this->auth()->id,
			$userQualification
		);

		if (!$surveyUser) {
			return new Exception('Parece que hubo un error al finalizar la encuesta', 500);
		}

		$surveyGuides = $this->guideSurveyRepository->findSurveyWithAvailableGuides($survey->id);

		$availableGuides =  [...array_filter((array)[...$surveyGuides], function ($guide, $key) {
			$currentGuide = $this->guideUserRepository->getCurrentGuideUser($guide->guide_id, $this->auth()->id, $guide->survey_id);
			return $currentGuide && !$currentGuide->status;
		}, ARRAY_FILTER_USE_BOTH)];

		return [
			'message' => 'La encuesta ha finalizado correctamente',
			'guide' => count($availableGuides) > 0 ? 
			$this->guideUserRepository->getCurrentGuideUser($availableGuides[0]->guide_id, $this->auth()->id, $availableGuides[0]->survey_id)
				: null,
		];
	}

	public function existSurveyInProgress()
	{
		$survey = $this->surveyRepository->getCurrentSurvey();
		if (!$survey) {
			return new Exception('No hay encuestas disponibles', 400);
		}

		$surveyGuides = $this->guideSurveyRepository->findSurveyWithAvailableGuides($survey->id);

		$availableGuides =  [...array_filter((array)[...$surveyGuides], function ($guide, $key) {
			$currentGuide = $this->guideUserRepository->getCurrentGuideUser($guide->guide_id, $this->auth()->id, $guide->survey_id);
			return $currentGuide && !$currentGuide->status;
		}, ARRAY_FILTER_USE_BOTH)];

		if (count($availableGuides) <= 0) {
			return new Exception('La encuesta ya ha sido contestada', 400);
		}

		$guideUser = $this->guideUserRepository->getCurrentGuideUser(
			$availableGuides[0]->guide_id,
			$this->auth()->id,
			$availableGuides[0]->survey_id
		);

		return $guideUser->status ? new Exception('La encuesta ya ha sido contestada', 400) : ['guide' => $guideUser];
	}

	public function finalizedGuideInsideSurvey(string $surveyId, string $guideId, int $totalUsers)
	{
		$totalSurveys = $this->guideUserRepository->countGudesUsersAvailable($surveyId, $guideId);

		if ($totalSurveys !== $totalUsers) {
			return new Exception('No es posible finalizar la guía porque existen usuarios sin contestar la guia', 400);
		}

		$guideSurvey = $this->guideSurveyRepository->findByGuideSurvey($surveyId, $guideId);

		if (!$guideSurvey) {
			return new Exception('La guia no exite o no es valida', 404);
		}
		if ($guideSurvey->status === GuideStatus::FINISHED->value) {
			return new Exception('La guia ya ha sido finalizada', 400);
		}
		if ($guideSurvey->status === GuideStatus::NOINITIALIZED->value) {
			return new Exception('La guia no se puede finalizar', 400);
		}

		$currentGuideSurvey = $this->guideSurveyRepository->finalizedGuideSurvey($surveyId, $guideId);

		$nextGuideUser = $this->guideSurveyRepository->startNextGuide();

		return [
			'current_guide' => $currentGuideSurvey,
			'next_guide'    => $nextGuideUser,
		];
	}

	public function findOneSurveyWithGuides(string $surveyId, string $guideId)
	{
		// $survey = $this->guideUserRepository->findOne($surveyId);
		// if (!$survey) return new Exception('El cuestionario no existe o no es valido', 404);
		return $this->guideUserRepository->findUserGuideBySurvey($surveyId, $guideId);
	}

	public function getTotalUsersInSurvey(string $surveyId)
	{
		// return $this->surveyUserRepository->countSurveyUserAnswers($surveyId);
	}

	public function findSurveyByNameAndAreas(
		string $surveyId,
		string $guideId,
		string $name,
		string $areaId,
		string $subareaId
	) {
		return $this->guideUserRepository->searchByNameAndAreas($surveyId, $guideId, $name, $areaId, $subareaId);
	}


	public function getSurveyGuideByArea(string $surveyId, string $guideId, string $areaId)
	{
		$survey = $this->guideUserRepository->searchByArea($surveyId, $guideId, $areaId);
		return !$survey ? new Exception('Parece que hubo un error al consultar la información', 400) : $survey;
	}

	public function getDetailsByUser(string $surveyId, string $userId, string $guideId)
	{
		$survey = $this->surveyRepository->findOne($surveyId);
		if (!$survey) {
			return new Exception('El cuestionario no existe o no es valido', 404);
		}
		$suerveyUser = $this->guideUserRepository->getDetailsByUser($surveyId, $userId, $guideId);
		return !$suerveyUser ? new Exception('La encuesta no esta disponible', 404) : $suerveyUser;
	}


	private function hasPreviousQuestion(mixed $answers, mixed $newBody): array
	{
		foreach ($answers as $index => $answer) {
			foreach ($newBody as $key => $newQuestion) {
				if ($answer['question_id'] === $newQuestion['question_id']) {
					$answers[$index] = $newQuestion;
					unset($newBody[$key]);
				}
			}
		}
		return [...$answers, ...$newBody];
	}


	private function validateSectionBinaryToSaveQuestion(Section $section, bool $qualification)
	{
		return [
			'question_id'        => uniqid('uuid'),
			'name'               => $section->question,
			'category'           => '',
			'section'            => $section->id,
			'domain'             => '',
			'dimension'          => '',
			'qualification'      => $qualification,
			'qualification_name' => '',
		];
	}

	private function validateQuestions(Question $question, bool|int $qualification)
	{
		return [
			'question_id' => $question->id,
			'name'        => $question->name,
			'category'    => [
				'id'            => $question->category->id ?? '',
				'name'          => $question->category->name ?? '',
				'qualification' => $this->parseQualificationData(
					$this->qualificationQuestionRepository->findQualificationByQuestion($question->id, Category::class)
				),
			],
			'section' => [
				'id'   => $question->section->id ?? '',
				'name' => $question->section->name ?? '',
			],
			'domain' => [
				'id'            => $question->domain->id ?? '',
				'name'          => $question->domain->name ?? '',
				'qualification' => $this->parseQualificationData(
					$this->qualificationQuestionRepository->findQualificationByQuestion($question->id, Domain::class)
				),
			],
			'dimension' => [
				'id'   => $question->dimension->id ?? '',
				'name' => $question->dimension->name ?? '',
			],
			'qualification'      => $qualification,
			'qualification_data' => $this->questionRepository->getQualification($question) ?? '',
		];
	}

	public function getLastSurveyByUser(string $userId)
	{
		return $this->guideUserRepository->findCurrentSurveyUser($userId);
	}

	public function attachGuidesToSurvey(Survey $survey, array $guidesId)
	{
		return $this->surveyRepository->setGuidesToNewSurvey($survey, $guidesId);
	}

	public function getInProgressGuideSurvey(string $surveyId, string $guideId)
	{
		$guideSurvey = $this->guideUserRepository->getDetailsSurveyUser($surveyId, $guideId);
		return $guideSurvey;
	}

	public function startGuideAndPauseOthersGuides(string $surveyId, string $guideId)
	{
		$guide = $this->guideSurveyRepository->findByGuideSurvey($surveyId, $guideId);
		if (!$guide) {
			return new Exception('El cuestionario que intentas comenzar no existe o no esta disponible', 400);
		}
		if ($this->canContinueGuide($surveyId, $guideId) > 0) {
			return new Exception('El cuestionario no puede continuar porque existen usuarios respondiendo un cuestionario', 400);
		}

		$guides = $this->guideSurveyRepository->startGuideAndPauseOtherGuides($guide);

		$guide = collect(...array_filter([...$guides], fn ($guide) => $guide['guide_id'] == $guideId));
		return ($guide && $guide['status'] === GuideStatus::INPROGRESS->value) ?
			['message' => 'El cuestionario cambio de estatus correctamente'] : new Exception('Parece que hubo un error al intentar comenzar el cuestionario', 400);
	}


	public function canContinueGuide(string $surveyId, string $guideId)
	{
		return $this->guideUserRepository->existGuidesInProgres($surveyId, $guideId);
	}

	public function existGuideInProgress(string $surveId)
	{
		return $this->guideSurveyRepository->existInProgressGuide($surveId);
	}

	private function calculateUserQualification(GuideUser $guideUser): int
	{
		return array_reduce(
			(array) $guideUser->answers,
			fn ($prev, $curr) => is_numeric($curr['qualification']) ? $prev + $curr['qualification'] : $prev
		) ?? 0;
	}

	private function parseQualificationData(mixed $body)
	{
		if (!$body) {
			return '';
		}
		return [
			'id'         => $body->qualificationable->id,
			'despicable' => $body->qualificationable->despicable,
			'low'        => $body->qualificationable->low,
			'middle'     => $body->qualificationable->middle,
			'high'       => $body->qualificationable->high,
			'very_high'  => $body->qualificationable->very_high,
		];
	}
}
