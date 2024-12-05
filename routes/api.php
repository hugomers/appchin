<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Storage;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/addFile',[FileController::class,'AddFile']);
Route::get('/getProducts',[ProductsController::class,'getProducts']);
// Route::get('/imagen/{ruta}', function($ruta){
//     if (!Storage::exists('public/'.$ruta)) {
//         abort(404);
//         return 'No existe la ruta'.$ruta;
//     }
//     return Storage::download('public/'.$ruta);
//     // return $ruta;
// })->where('ruta', '.*');;
Route::get('/imagen/{ruta}', function($ruta){
    if (!Storage::exists('public/'.$ruta)) {
        abort(404, 'No existe la ruta: '.$ruta);
    }
    return response()->file(storage_path('app/public/'.$ruta), [
        'Content-Type' => mime_content_type(storage_path('app/public/'.$ruta)),
        'Content-Disposition' => 'inline',
    ]);
})->where('ruta', '.*');;

Route::post('/insProduct',[ProductsController::class,'insProduct']);
Route::get('/export', [ExportController::class, 'export'])->name('export');


