<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Expenses;

class ProductsController extends Controller
{
    public function getProducts(){
        $products = Product::where('_download',0)->get();
        $expenses = Expenses::all();
        foreach($products as $product){
            $productId = $product['id'];
            $folderName = $product['picture'];
            $folderPathProduct = public_path("storage/{$productId}/{$folderName}");
            $folderNameProv = $product['provider'];
            $folderPathProvider = public_path("storage/{$productId}/{$folderNameProv}");

            if (!file_exists($folderPathProvider) || !is_dir($folderPathProvider)) {
                $product['fileProvider'] = [];
            }
            if (!file_exists($folderPathProduct) || !is_dir($folderPathProduct)) {
                $product['fileProduct'] = [];
            }
            $files = array_values(array_diff(scandir($folderPathProduct), ['.', '..'])); // Excluye `.` y `..`
            $filesProv = array_values(array_diff(scandir($folderPathProvider), ['.', '..'])); // Excluye `.` y `..`


            $filesWithUrls = array_map(function ($file) use ($productId, $folderName) {
                    return  "{$productId}/{$folderName}/{$file}";
            }, $files);

            $filesWithUrlsProv = array_map(function ($file) use ($productId, $folderNameProv) {
                return  "{$productId}/{$folderNameProv}/{$file}";
            }, $filesProv);

            $product['fileProduct']= $filesWithUrls;
            $product['fileProvider']= $filesWithUrlsProv;

        }
        $res = [
            "products"=>$products,
            "expenses"=>$expenses
        ];
        return response()->json($res,200);

    }

    public function insProduct(Request $request){
        $inst = $request->all();
        // return public_path();
        $product = new Product;
        $product->code = $inst['code'];
        $product->description  = $inst['description'];
        $product->chinesse_cost = $inst['chinesse_cost'];
        $product->taxes = $inst['taxes'];
        $product->freight = $inst['freight'];
        $product->measures = $inst['measures'];
        $product->mexican_cost = $inst['mexican_cost'];
        $product->pieces = $inst['pieces'];
        $product->_download = $inst['_download'];
        $product->notes = $inst['notes'];
        $product->maker = $inst['maker'];
        $product->save();
        $res = $product->fresh()->toArray();
        if ($request->hasFile("picture")) {
            // return $request->hasFile("picture");
            $folderName = uniqid();
            $folderPath = public_path('storage/'.$res['id'].'/'.$folderName);
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            $files = $request->file("picture");
            $count = 1;
            foreach ($files as $file) {
                $fileName = $count.$file->getClientOriginalName();
                $file->move($folderPath, $fileName);
                $count++;
            }
            $product->picture = $folderName;

        }
        if ($request->hasFile("provider")) {
            $folderName = uniqid();
            $folderPath = public_path('storage/'.$res['id'].'/'.$folderName);

            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            $files = $request->file("provider");
            $count = 1;
            foreach ($files as $file) {
                $fileName = $count.$file->getClientOriginalName();
                $file->move($folderPath, $fileName);
                $count++;
            }
            $product->provider = $folderName;
        }
        $product->save();
        $res = $product->fresh();


        return response()->json($res);
    }

    public function updateProduct(Request $request){
        $products= $request->products;
        foreach($products as $product){
            $update = Product::find($product['id']);
            $update->_download = 1;
            $update->save();
        }
        $prod = Product::where('_download',0)->get();
        $expenses = Expenses::all();
        foreach($prod as $product){
            $productId = $product['id'];
            $folderName = $product['picture'];
            $folderPathProduct = public_path("storage/{$productId}/{$folderName}");
            $folderNameProv = $product['provider'];
            $folderPathProvider = public_path("storage/{$productId}/{$folderNameProv}");

            if (!file_exists($folderPathProvider) || !is_dir($folderPathProvider)) {
                $product['fileProvider'] = [];
            }
            if (!file_exists($folderPathProduct) || !is_dir($folderPathProduct)) {
                $product['fileProduct'] = [];
            }
            $files = array_values(array_diff(scandir($folderPathProduct), ['.', '..'])); // Excluye `.` y `..`
            $filesProv = array_values(array_diff(scandir($folderPathProvider), ['.', '..'])); // Excluye `.` y `..`


            $filesWithUrls = array_map(function ($file) use ($productId, $folderName) {
                    return  "{$productId}/{$folderName}/{$file}";
            }, $files);

            $filesWithUrlsProv = array_map(function ($file) use ($productId, $folderNameProv) {
                return  "{$productId}/{$folderNameProv}/{$file}";
            }, $filesProv);

            $product['fileProduct']= $filesWithUrls;
            $product['fileProvider']= $filesWithUrlsProv;

        }
        $res = [
            "products"=>$prod,
            "expenses"=>$expenses
        ];
        return response()->json($res,200);
    }
}
