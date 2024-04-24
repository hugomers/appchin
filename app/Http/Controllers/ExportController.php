<?php

namespace App\Http\Controllers;

use App\Exports\ProductReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Product;

class ExportController extends Controller
{

        public function export()
        {
            $export =  Excel::download(new ProductReport, 'filename.xlsx');
            if($export){
                $product = Product::where('_download',0)->update(['_download'=>1]);
                if($product){
                    return $export;
                }else{
                    return response()->json('No se modificaron los productos');
                }
            }else{
                return $export;
            }

        }

}
