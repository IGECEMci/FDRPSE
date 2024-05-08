<?php
/**
 * Son test unitarios para verificar y validar el funcionamiento de ciertas funciones para testear que una función se comporte de la manera que 
 * se espara.
 */

namespace Test\traits;

use App\shared\traits\ExcelTrait;
use PHPUnit\Framework\TestCase;

final class GetNameOfQualificationTest extends TestCase
{
    use ExcelTrait; 

    public function test_get_name_of_qualificartion()
    {
        $qualifications = [
            "id" => 1,
            "despicable" => "3",
            "low" => "5",
            "middle" => "7",
            "high" => "9",
            "very_high" => "10"
        ];

        $name = $this->getNameOfQualificationGuide(4, (object)$qualifications);

        $this->assertSame('Bajo', $name);
    }

    public function test_get_answer_by_number()
    {
        $QUALIFICATION = [
            'always_op'        => 0,
            'almost_alwyas_op' => 1,
            'sometimes_op'     => 2,
            'almost_never_op'  => 3,
            'never_op'         => 4,
        ];
        $answer = $this->getNameOfQualifications(4, $QUALIFICATION);

        $this->assertSame('Nunca', $answer);
    }

}
