<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Expenses;

class ProductsController extends Controller
{
    public function getProducts(){
        $products = Product::all();
        $expenses = Expenses::all();
        $res = [
            "products"=>$products,
            "expenses"=>$expenses
        ];
        return response()->json($res,200);
    }

    public function insProduct(Request $request){
        $product = Product::insert($request->all());
        return response()->json($product);
    }
}
