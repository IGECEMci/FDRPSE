<?php

/**
 * El trait es el responsable de parsear la información que recibe dependiendo de la lógica de negocio, que en este caso es la lógica que sigue los cuestionarios y la norma.
 */
namespace App\shared\traits;

trait ExcelTrait
{
    public function getNameOfQualifications(mixed $qualification, mixed $qualificationData)
    {
        if (gettype($qualification) === 'boolean') {
            return $qualification ? 'Si' : 'No';
        }

        $QUALIFICATION_NAME = [
            'always_op'        => 'Siempre',
            'almost_alwyas_op' => 'Casi siempre',
            'sometimes_op'     => 'Algunas veces',
            'almost_never_op'  => 'Casi nunca',
            'never_op'         => 'Nunca',
        ];
    

        return $QUALIFICATION_NAME[array_search($qualification, (array) $qualificationData)];
    }

    public function getNameOfQualificationGuide(int $total, object $qualification)
    {
        return match (true) {
            ($total < $qualification->despicable)                                        => "Despreciable o nulo",
            ($total >= $qualification->despicable && $total < $qualification->low)       => "Bajo",
            ($total >= $qualification->low && $total < $qualification->middle)           => "Medio",
            ($total >= $qualification->middle && $total < $qualification->high)          => "Alto",
            ($total >= $qualification->high)                                             => "Muy alto",
            default                                                                      => "NA",
        };
    }

    public function getColorByQualification(string $qualification)
    {
        return match (true) {
            ($qualification === "Despreciable o nulo")      => "#7CBBFE",
            ($qualification === "Bajo")                     => "#86E399",
            ($qualification === "Medio")                    => "#EEEC8D",
            ($qualification === "Alto")                     => "#EEC28D",
            ($qualification === "Muy alto")                 => "#FF6767",
            default                                         => "#FFFFFF",
        };
    }

    public function calculcateQualificationByCriteria(string $criteria, mixed $answer)
    {
        return array_reduce($answer, function ($prev, $curr) use ($criteria) {
            if (!isset($curr[$criteria]['name']) || !$curr[$criteria]['name']) {
                return $prev;
            }
            ['qualification' => $qualification] = $curr;

            if (isset($prev[$curr[$criteria]['name']])) {
                $prev[$curr[$criteria]['name']]['qualification'] = $prev[$curr[$criteria]['name']]['qualification'] + $qualification;
            } else {
                $prev[$curr[$criteria]['name']] = [
                    'qualification' => $qualification,
                    'qualifications' => $curr[$criteria]['qualification'],
                ];
            }
            return $prev;
        }, []);
    }
}
