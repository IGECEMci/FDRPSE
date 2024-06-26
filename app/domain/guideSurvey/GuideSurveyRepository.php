<?php

namespace App\domain\guideSurvey;

use App\domain\BaseRepository;
use App\domain\section\Section;
use Illuminate\Database\Eloquent\Collection;

interface GuideSurveyRepository extends BaseRepository
{
	public function findGuideInProgress(): ?GuideSurvey;
	public function findQuestionInsideGuide(GuideSurvey $guideSurvey, string $questionId): ?Section;
	public function finalizedGuideSurvey(string $surveyId, string $guideId): ?GuideSurvey;
	public function startNextGuide(): ?GuideSurvey;
	public function findByGuideSurvey(string $surveyId, string $guideId): ?GuideSurvey;
	public function startGuideAndPauseOtherGuides(GuideSurvey $guideSurvey): array;
	public function existInProgressGuide(string $surveyId): int;
	public function findAvailableGuides(string $surveyId, string $guideId): ?GuideSurvey;
	public function findSurveyWithAvailableGuides(string $surveyId): Collection;
}
