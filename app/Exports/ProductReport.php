<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;


class ProductReport extends DefaultValueBinder implements FromCollection,  WithDrawings, ShouldAutoSize, WithEvents, WithHeadings, WithColumnFormatting, WithCustomValueBinder
{
    protected $products;

    public function collection(){
        $products = Product::all();
        $data = collect($products)->map(function ($fila) {
            return [
                "picture"=>$fila->picture,
                "code"=>$fila->code,
                "description"=>$fila->pieces,
                "provider"=>$fila->provider,
                "pieces"=>$fila->pieces.' PZS',
                "measures"=>$fila->measures,
                "chinesse_cost"=>$fila->chinesse_cost,
                "mexican_cost"=>$fila->mexican_cost,
                "created_at"=> Date::dateTimeToExcel($fila->created_at)
            ];
        });
        return $data;
    }

    public function drawings(){
        $products = Product::all();
        $row = 2;
            foreach($products as $product){
                if (!$imageResource = @imagecreatefromstring(file_get_contents('http://192.168.10.112:1920/appchin/storage/app'.$product->picture))) {
                    $imageResource = @imagecreatefromstring(file_get_contents('http://192.168.10.112:1920/appchin/storage/app/vacio.jpg'));
                }
                $drawing = new MemoryDrawing();
                $drawing->setName($product->code);
                $drawing->setDescription($product->description);
                $drawing->setImageResource($imageResource);
                $drawing->setWidth(90);
                $drawing->setCoordinates('A'.$row);
                $row++;
                $drawings[] = $drawing;
            }
            return $drawings;
    }

    public function registerEvents(): array{
        // return [
        //     AfterSheet::class => function(AfterSheet $event) {
        //         $products = Product::all();
        //         $row = 2;
        //         foreach($products as $product){
        //             $event->sheet->getRowDimension($row)->setRowHeight(90);
        //             $row++;
        //         }

        //     },
        // ];

        return [
            AfterSheet::class => function(AfterSheet $event) {
                $filas = ['A','B','C','D','E','F','G','H','I'];
                $products = Product::all();
                $lastColumn = $event->sheet->getDelegate()->getHighestColumn();
                $lastRow = $event->sheet->getDelegate()->getHighestRow();
                $filterRange = 'A1:' . $lastColumn . $lastRow;
                foreach($filas as $column){
                    for($row = 2; $row <= count($products)+1; $row++){
                        $event->sheet->getStyle($column.$row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'B2D5E6'],
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                        ]);
                        $event->sheet->getStyle($column.'1')->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '3498DB'],
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                        ]);

                        $event->sheet->getRowDimension($row)->setRowHeight(90);
                    }
                }
                $event->sheet->getDelegate()->setAutoFilter($filterRange);
            },
        ];
    }

    public function headings(): array{
        return [
            "IMAGEN",
            "CODIGO",
            "DESCRIPCION",
            "PROVEEDOR",
            "PXC",
            "CUBICAJE",
            "COSTO CHINO",
            "COSTO MEXICANO",
            "CREACION",
        ];
    }
    public function columnFormats(): array{
        return [
            'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }
}
