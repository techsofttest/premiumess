<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandClassificationSeeder extends Seeder
{
    public function run(): void
    {
        $classifications = [
            'Designer Houses' => ['Dior', 'Chanel', 'Gucci', 'Yves Saint Laurent', 'Versace'],
            'Prestige & Niche' => ['Creed', 'Tom Ford', 'Maison Francis Kurkdjian', 'Jo Malone London'],
            'Classic Elegance' => ['Hermès', 'Givenchy', 'Prada', 'Bvlgari', 'Montblanc'],
        ];

        foreach ($classifications as $class => $brands) {
            foreach ($brands as $brandName) {
                $brand = Brand::where('name', 'LIKE', $brandName)->first();
                if (!$brand) {
                    $brand = new Brand();
                    $brand->name = $brandName;
                }
                $brand->classification = $class;
                $brand->save();
                $this->command->info("Brand: {$brand->name} => {$brand->classification}");
            }
        }
    }
}
