<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        DB::table('products')->delete();
        $products = array(
            (object) [
                'name'=>'Hani Big Coat', 'price'=>'69.9', 'description'=>'Super warm winter coat!', 'image'=>'008.jpeg', 'catid'=>'1', 'stock'=>'5',
            ],
            (object) [
                'name'=>'Nike Dunk Low', 'price'=>'69.0', 'description'=>'It is a good shoes! Buy it!', 'image'=>'001.jpg', 'catid'=>'4', 'stock'=>'3',
             ],
             (object) [
                'name'=>'Chicago Hoodie', 'price'=>'72.2', 'description'=>'Super new style in Chicago!!!', 'image'=>'002.jpeg', 'catid'=>'2', 'stock'=>'0',
             ],
             (object) [
                'name'=>'Men South Korea Shorts', 'price'=>'46.3', 'description'=>'Very cool!', 'image'=>'006.jpeg', 'catid'=>'3', 'stock'=>'2',
             ],
             (object) [
                'name'=>'Basic Tube Top', 'price'=>'89.0', 'description'=>'Very hot summer outfit', 'image'=>'003.jpeg', 'catid'=>'2', 'stock'=>'2',
             ],
             (object) [
                'name'=>'Fashion Pikachu Hoodie', 'price'=>'69.9', 'description'=>'Very cute anime style hoodie', 'image'=>'004.jpg', 'catid'=>'2', 'stock'=>'0',
             ],
             (object) [
                'name'=>'Sport Short Pants', 'price'=>'29.9', 'description'=>'Very nice sporty style!', 'image'=>'005.jpeg', 'catid'=>'3', 'stock'=>'2',
             ],
             (object) [
                'name'=>'Korea Oppa Coat', 'price'=>'79.9', 'description'=>'Very warm oppa coat!', 'image'=>'007.jpeg', 'catid'=>'1', 'stock'=>'3',
             ],
          );

        foreach ($products as $product) {
            Product::create([
                'catid' => "$product->catid",
                'name' => $product->name,
                'price' => $product->price,
                'description' => $product->description,
                'stock' => $product->stock,
                'image' => $product->image,
                'thumbnail' => '',
            ]);
        }
    }
}
