<?php
/**
 * El Trait es el responsable para crear archivos de excel para descargar un reporte por área o usuario
 */
namespace App\shared\traits;

use App\infrastructure\adapters\ExcelAdapter;

trait CreateExcelReport
{
    use ExcelTrait;

    public function createCommonHeaders(ExcelAdapter $excelAdapter, mixed $guide)
    {
        $excelAdapter->setHeader([
            '<center><middle><b><style height="30" bgcolor="#059669" color="#FFFFFF">' . $guide['guide']['name'] . '</style></b></middle></center>'
        ])->mergeCellDocument('A1:E1');

        $excelAdapter->setHeader([
            '<b><center><middle><style height="30" border="#000000" bgcolor="#059669" color="#FFFFFF">Usuario:</middle></center></style></b>',
            "<middle><left>" . $guide['user']['nombre']
                . " " . $guide['user']['apellidoP'] . " " . $guide['user']['apellidoM'] . "</left></middle>",
            null
        ])->mergeCellDocument('B2:E2');

        $excelAdapter->setHeader([
            '<b><center><middle><style height="30" border="#000000" bgcolor="#059669" color="#FFFFFF">Área:</middle></center></style></b>',
            "<middle><left>" . $guide['user']['area']['nombreArea'] . "</left></middle>",
            null
        ])->mergeCellDocument('B3:E3');

        if ($guide['guide']['gradable']) {
            $excelAdapter->setHeader([
                '<center><left><style height="30"></style></left></center>',
            ]);
            $name = $this->getNameOfQualificationGuide($guide['total'], $guide['guide']['qualification']);
            $excelAdapter->setHeader([
                "<middle><b>CALIFICACIÓN DEL CUESTIONARIO:</b></middle>",
                "<middle><left>Pts:" . $guide['total'] . "</left></middle>",
                '<middle><center><style height="30" bgcolor="' . $this->getColorByQualification($name) . '">' . $name . "</style></center></middle>",
            ]);
            $excelAdapter->setHeader([
                '<center><left><style height="30"></style></left></center>',
            ]);
            $qualification = $this->calculcateQualificationByCriteria('category', $guide['answers']);
            foreach ($qualification as $key => $value) {
                $name = $this->getNameOfQualificationGuide($value['qualification'], (object)$value['qualifications']);
                $excelAdapter->setHeader([
                    "<middle><b>CATEGORÍA - "  . $key . ": </b></middle>",
                    "<middle><left>Pts:" . $value['qualification'] . "</left></middle>",
                    '<middle><center><style height="30" bgcolor="' . $this->getColorByQualification($name) . '">' . $name . "</style></center></middle>",
                ]);
            }
            $excelAdapter->setHeader([
                '<center><left><style height="30"></style></left></center>',
            ]);
            $qualification = $this->calculcateQualificationByCriteria('domain', $guide['answers']);
            foreach ($qualification as $key => $value) {
                $name = $this->getNameOfQualificationGuide($value['qualification'], (object)$value['qualifications']);
                $excelAdapter->setHeader([
                    "<middle><b>DOMINIO - "  . $key . ":</b></middle>",
                    "<middle><left>Pts:" . $value['qualification'] . "</left></middle>",
                    '<middle><center><style height="30" bgcolor="' . $this->getColorByQualification($name) . '">' . $name . "</style></center></middle>",
                ]);
            }
        }
        $excelAdapter->setHeader([
            '<center><left><style height="30"></style></left></center>',
        ]);

        $excelAdapter->setHeader([
			'<center><middle><b><style height="30" bgcolor="#059669" color="#FFFFFF">Pregunta</style></b></middle></center>',
			'<center><middle><b><style height="30" bgcolor="#059669" color="#FFFFFF">Calificación</style></b></middle></center>',
			'<center><middle><b><style height="30" bgcolor="#059669" color="#FFFFFF">Categoría</style></b></middle></center>',
			'<center><middle><b><style height="30" bgcolor="#059669" color="#FFFFFF">Dominio</style></b></middle></center>',
			'<center><middle><b><style height="30" bgcolor="#059669" color="#FFFFFF">Dimensión</style></b</middle></center>>'
		]);
    }
    
    public function setBodyReport(mixed $guide)
	{
		$this->excelAdapter->setData(array_map(function ($__, array $answer,) {
			return [
				'<middle><style  height="30">' . $answer['name'] . '</style></middle>',
				"<center><b>" . $this->getNameOfQualifications($answer['qualification'], $answer['qualification_data'] ?? "") . "</b></center>",
				isset($answer['category']['name']) ? "<middle>" . $answer['category']['name'] . "</middle>"  : "",
				isset($answer['domain']['name']) ? "<middle>" . $answer['domain']['name'] . "</middle>"  : "",
				isset($answer['dimension']['name']) ? "<middle>" . $answer['dimension']['name'] . "</middle>"  : "",
			];
		}, (array) array_keys($guide['answers']), array_values($guide['answers'])));
	}

}
