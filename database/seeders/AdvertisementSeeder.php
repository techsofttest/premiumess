<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Advertisement;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Advertisement::firstOrCreate(
            ['location' => 'home_top'],
            [
                'name' => 'Home Page Top Banner',
                'title' => 'Special Sale!',
                'banner' => 'placeholder.jpg',
                'url' => '#',
            ]
        );

        Advertisement::firstOrCreate(
            ['location' => 'sidebar'],
            [
                'name' => 'Sidebar Ad Space',
                'title' => 'Check our new arrivals',
                'banner' => 'placeholder_sidebar.jpg',
                'url' => '#',
            ]
        );
    }
}
