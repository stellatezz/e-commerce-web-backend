<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->delete();
        $categories = array(
            (object) [
                'name'=>'Coat',
            ],
            (object) [
                'name'=>'Top',
            ],
            (object) [
                'name'=>'Bottom',
            ],
            (object) [
                'name'=>'Shoes',
            ]
          );

        foreach ($categories as $category) {
            Category::create([
                'name' => $category->name,
            ]);
        }
    }
}
