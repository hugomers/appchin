<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    public function AddFile(Request $request){
        if($request->hasFile('files')){
            $file = $request->file('files');
            $file->storeAs($request->__key);
            return $request->__key;
        }
    }
}
