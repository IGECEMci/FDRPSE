<?php

namespace App\infrastructure\controllers;

use App\kernel\controllers\Controller;
use App\application\survey\SurveyUseCase;
use App\domain\survey\Survey;
use App\domain\surveyUser\SurveyUser;
use App\infrastructure\requests\survey\SaveQuestionRequest;

class SurveyController extends Controller
{

    public function __construct(private readonly SurveyUseCase $surveyUseCase)
    {
    }

    public function getAllSurveys()
    {
        $this->response($this->surveyUseCase->getAllSurveys());
    }

    public function startSurvey()
    {
        $this->response($this->surveyUseCase->startNewSurvey());
    }

    public function saveUserAnswers()
    {
        $this->validate(SaveQuestionRequest::rules(), SaveQuestionRequest::messages());
        $this->response($this->surveyUseCase->saveAnswers($this->request()));
    }

    public function startSurveyByUser()
    {
        $this->response($this->surveyUseCase->startSurveyByUser());
    }

    public function finishUserSurvey()
    {
        $this->response($this->surveyUseCase->finalizeSurveyByUser());
    }

    public function getCurrentSurvey()
    {
        $this->response($this->surveyUseCase->getInProgressSurvey());
    }

    public function getSurveyById(string $surveyId)
    {
        $suervey = Survey::find($surveyId);
        $surveyUser = SurveyUser::where('survey_id', $suervey->id)->with('user:id,nombre,userName')->get(['user_id', 'answers', 'total'])->toArray();
    

        $total = 0;
        foreach ($surveyUser as $key => $survey) {
            $total = $total + $survey['total'];
        }
         $this->response(['users' => $surveyUser, 'total' => $total/count($surveyUser)]);

    }
}
