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
                // "picture"=>$fila->picture,
                "picture"=>'',
                "code"=>$fila->code,
                "description"=>$fila->pieces,
                // "provider"=>$fila->provider,
                "provider"=>'',
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
                $firstdrawing = new Drawing();
                $firstdrawing->setName($product->code);
                $firstdrawing->setDescription($product->description);
                // $firstdrawing->setImageResource($imageResource);
                if(is_null($product->picture)){
                    $firstdrawing->setPath('/var/www/html/appchin/storage/app/vacio.jpg');
                    // $firstdrawing->setPath('C:/laragon/www/appchin/storage/app/vacio.jpg');
                }else{
                    $firstdrawing->setPath('/var/www/html/appchin/storage/app'.$product->picture);
                    // $firstdrawing->setPath('C:/laragon/www/appchin/storage/app'.$product->picture);
                }
                $firstdrawing->setWidth(90);
                $firstdrawing->setCoordinates('A'.$row);
                $drawings[] = $firstdrawing;

                $secondrawing = new Drawing();
                $secondrawing->setName($product->provider);
                $secondrawing->setDescription($product->code);
                // $secondrawing->setImageResource($imageResource);
                if(is_null($product->provider)){
                    $secondrawing->setPath('/var/www/html/appchin/storage/app/vacio.jpg');
                    // $secondrawing->setPath('C:/laragon/www/appchin/storage/app/vacio.jpg');
                }else{
                    $secondrawing->setPath('/var/www/html/appchin/storage/app'.$product->provider);
                    // $secondrawing->setPath('C:/laragon/www/appchin/storage/app'.$product->provider);
                }
                $secondrawing->setWidth(90);
                $secondrawing->setCoordinates('D'.$row);
                $row++;
                $drawings[] = $secondrawing;
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
