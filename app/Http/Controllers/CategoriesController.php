<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    //
    public function index()
    {
        return Category::all();
    }
    public function getWithCatid($catid)
    {
        $results = DB::select('select * from categories where catid = ?', array($catid));
        return $results;
    }
    public function store()
    {
        request()->validate([
            'name' => 'required',
        ]);
        try{
            $result = Category::create([
                'name' => request('name'),
            ]);
            return response()->json([
                'message' => $result,
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'message' => 'error occur'
            ], 500);
        }
    }
    public function update($catid)
    {
        request()->validate([
            'name' => 'required',
        ]);
        try{
            $result = DB::update('update categories set name = ? where catid = ?', array(request()->name, $catid));

            return response()->json([
                'message' => $result
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'message' => 'error occur'
            ], 500);
        }
    }
    public function destroy($catid)
    {
        try {
            $result = DB::table('categories')->where('catid', '=', $catid)->delete();
            
            return response()->json([
                'message' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'error occur'
            ]);
        }
    }
}
