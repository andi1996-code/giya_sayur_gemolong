<?php
// Script untuk fix product images yang broken

// Include Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

// Update semua produk yang image-nya tidak ada atau path lama
$products = Product::all();
$count = 0;

foreach ($products as $product) {
    $imagePath = 'storage/' . $product->image;
    $fullPath = public_path($imagePath);

    // Jika file tidak ada atau path lama, update ke default
    if (empty($product->image) || strpos($product->image, 'product-default.jpg') !== false || !file_exists($fullPath)) {
        $product->update(['image' => 'products/default.webp']);
        $count++;
        echo "Updated: {$product->name} -> products/default.webp\n";
    }
}

echo "\nTotal produk yang di-update: {$count}\n";
?>
