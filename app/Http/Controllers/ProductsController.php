<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;

class ProductsController extends Controller
{
    //
    public function index()
    {
        return Product::all();
    }

    public function getWithCatid($catid)
    {
        $results = DB::select('select * from products where catid = ?', array($catid));
        return $results;
    }

    public function getWithPid($pid)
    {
        $results = DB::select('select * from products where pid = ?', array($pid));
        return $results;
    }

    public function store()
    {
        request()->validate([
            'catid' => 'required',
            'name' => 'required',
            'price' => 'required',
            'description' => 'required',
            'stock' => 'required',
            'image' => 'required|image',
        ]);

        try{
            $destPath = 'product/image';
            $destinationPath = public_path('/storage/product/thumbnail');
            $randomStr = Str::random();
            $imageName = $randomStr.'.'.request()->image->getClientOriginalExtension();
            $res = Storage::disk('public')->putFileAs($destPath, request()->image,$imageName);
            $tumbImg = Image::make(request()->image)->resize(110, 130);
            $tumbImgName = $randomStr.'_thumbnail.jpg';
            $tumbImg->save($destinationPath.'/'.$tumbImgName);
            
            $result = Product::create([
                'catid' => request('catid'),
                'name' => request('name'),
                'price' => request('price'),
                'description' => request('description'),
                'stock' => request('stock'),
                'image' => $imageName,
                'thumbnail' => $tumbImgName,
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

    public function update($pid)
    {
        request()->validate([
            'name' => 'required',
            'catid' => 'required',
            'price' => 'required',
            'description' => 'required',
            'stock' => 'required',
        ]);
        try{
            $result = DB::update('update products set name = ?, catid = ?, price = ?, description = ?, stock = ? where 
            pid = ?', array(request()->name, request()->catid, request()->price, request()->description, request()->stock, $pid));

            if(request()->hasFile('image')){
                $oldImage = DB::select('select image from products where pid = ?', array($pid));
                if($oldImage[0]->image){
                    $oldImage = $oldImage[0]->image;
                    $exists = Storage::disk('public')->exists("product/image/{$oldImage}");
                    if($exists){
                        Storage::disk('public')->delete("product/image/{$oldImage}");
                    }
                }
                $oldTumb = DB::select('select thumbnail from products where pid = ?', array($pid));
                if($oldTumb[0]->thumbnail){
                    $oldTumb = $oldTumb[0]->thumbnail;
                    $isExists = Storage::disk('public')->exists("product/thumbnail/{$oldTumb}");
                    if($isExists){
                        Storage::disk('public')->delete("product/thumbnail/{$oldTumb}");
                    }
                }
                $destinationPath = public_path('/storage/product/thumbnail');
                $randomStr = Str::random();
                $imageName = $randomStr.'.'.request()->image->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('product/image', request()->image,$imageName);
                $tumbImg = Image::make(request()->image)->resize(110, 130);
                $tumbImgName = $randomStr.'_thumbnail.jpg';
                $tumbImg->save($destinationPath.'/'.$tumbImgName);
                $result = DB::update('update products set image = ?, thumbnail = ? where 
                pid = ?', array($imageName, $tumbImgName, $pid));
    
            }

            return response()->json([
                'message' => $result
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'message' => 'error occur'
            ], 500);
        }
    }

    public function destroy($pid)
    {
        try {
            $oldImage = DB::select('select image from products where pid = ?', array($pid));
            if($oldImage[0]->image){
                $oldImage = $oldImage[0]->image;
                $isExists = Storage::disk('public')->exists("product/image/{$oldImage}");
                if($isExists){
                    Storage::disk('public')->delete("product/image/{$oldImage}");
                }
            }
            $oldTumb = DB::select('select thumbnail from products where pid = ?', array($pid));
            if($oldTumb[0]->thumbnail){
                $oldTumb = $oldTumb[0]->thumbnail;
                $isExists = Storage::disk('public')->exists("product/thumbnail/{$oldTumb}");
                if($isExists){
                    Storage::disk('public')->delete("product/thumbnail/{$oldTumb}");
                }
            }
            $result = DB::table('products')->where('pid', '=', $pid)->delete();

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
