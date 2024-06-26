<?php

namespace App\domain\guideSurvey;

use App\domain\guide\Guide;
use App\domain\survey\Survey;
use Illuminate\Database\Eloquent\Relations\Pivot;

enum GuideStatus: int
{
	case NOINITIALIZED = 0;
	case INPROGRESS    = 1;
	case FINISHED      = 2;
	case PAUSED        = 3;
}
final class GuideSurvey extends Pivot
{
	protected $table    = 'guide_survey';
	protected $fillable = ['guide_id', 'survey_id', 'status', 'qualification'];
	protected $casts    = ['qualification' => 'json'];


	public function guides()
	{
		return $this->belongsTo(Guide::class, 'guide_id');
	}

	public function surveys()
	{
		return $this->belongsTo(Survey::class, 'survey_id');
	}
}
