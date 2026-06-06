<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate('vendor');
        Role::findOrCreate('customer');

        $brands = collect([
            'Michelin', 'Bridgestone', 'Goodyear', 'Continental', 'Pirelli',
        ])->map(fn (string $name) => Brand::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true],
        ));

        $categories = collect([
            'Passenger', 'SUV/Truck', 'Performance', 'Winter',
        ])->map(fn (string $name) => Category::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true],
        ));

        $vendor = User::query()->firstOrCreate(
            ['email' => 'vendor@tiremarket.test'],
            [
                'name' => 'Demo Vendor',
                'password' => Hash::make('password'),
                'is_vendor' => true,
                'shop_name' => 'Metro Tire Supply',
                'commission_rate' => 12.50,
            ],
        );
        $vendor->assignRole('vendor');

        $samples = [
            ['sku' => 'MIC-PIL-2254517', 'title' => 'Michelin Pilot Sport 4', 'width' => 225, 'aspect_ratio' => 45, 'rim_diameter' => 17, 'price' => 189.99, 'season' => 'performance'],
            ['sku' => 'BRI-TUR-2354518', 'title' => 'Bridgestone Turanza QuietTrack', 'width' => 235, 'aspect_ratio' => 45, 'rim_diameter' => 18, 'price' => 164.50, 'season' => 'all-season'],
            ['sku' => 'GOO-ASS-2756520', 'title' => 'Goodyear Assurance WeatherReady', 'width' => 275, 'aspect_ratio' => 65, 'rim_diameter' => 20, 'price' => 212.00, 'season' => 'all-season'],
            ['sku' => 'CON-WIN-2156016', 'title' => 'Continental VikingContact 7', 'width' => 215, 'aspect_ratio' => 60, 'rim_diameter' => 16, 'price' => 138.75, 'season' => 'winter'],
            ['sku' => 'PIR-PZE-2454019', 'title' => 'Pirelli P Zero', 'width' => 245, 'aspect_ratio' => 40, 'rim_diameter' => 19, 'price' => 249.99, 'season' => 'summer'],
            ['sku' => 'MIC-DEF-1956515', 'title' => 'Michelin Defender2', 'width' => 195, 'aspect_ratio' => 65, 'rim_diameter' => 15, 'price' => 119.99, 'season' => 'all-season'],
        ];

        foreach ($samples as $index => $sample) {
            Product::query()->updateOrCreate(
                [
                    'vendor_id' => $vendor->id,
                    'sku' => $sample['sku'],
                ],
                [
                    'brand_id' => $brands[$index % $brands->count()]->id,
                    'category_id' => $categories[$index % $categories->count()]->id,
                    'title' => $sample['title'],
                    'description' => 'Demo listing for marketplace launch.',
                    'price' => $sample['price'],
                    'stock' => 24,
                    'width' => $sample['width'],
                    'aspect_ratio' => $sample['aspect_ratio'],
                    'rim_diameter' => $sample['rim_diameter'],
                    'season' => $sample['season'],
                    'is_active' => true,
                ],
            );
        }
    }
}
