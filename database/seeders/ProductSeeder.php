<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Product 1', 'price' => 12.99],
            ['name' => 'Product 2', 'price' => 2.99],
            ['name' => 'Product 3', 'price' => 55.99],
            ['name' => 'Product 4', 'price' => 99.99],
            ['name' => 'Product 5', 'price' => 1.99],
            ['name' => 'Product 6', 'price' => 12.99],
            ['name' => 'Product 7', 'price' => 15.99],
            ['name' => 'Product 8', 'price' => 29.99],
            ['name' => 'Product 9', 'price' => 33.99],
            ['name' => 'Product 10', 'price' => 62.99],
            ['name' => 'Product 11', 'price' => 42.99],
            ['name' => 'Product 12', 'price' => 112.99],
            ['name' => 'Product 13', 'price' => 602.99],
            ['name' => 'Product 14', 'price' => 129.99],
            ['name' => 'Product 15', 'price' => 1200.99],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
