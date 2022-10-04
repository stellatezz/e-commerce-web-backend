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
            $f_name = filter_var(request('name'), FILTER_SANITIZE_STRING);

            $result = Category::create([
                'name' => $f_name,
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
            $f_name = filter_var(request('name'), FILTER_SANITIZE_STRING);
            $f_catid = filter_var($catid, FILTER_SANITIZE_STRING);

            $result = DB::update('update categories set name = ? where catid = ?', array($f_name, $f_catid));

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
