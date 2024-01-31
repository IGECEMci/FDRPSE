<?php

namespace App\domain\surveyUser;

use App\domain\survey\Survey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SurveyUser extends Model
{


    protected $table = 'survey_users';
    protected $fillable = ['survey_id', 'user_id', 'answers'];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}