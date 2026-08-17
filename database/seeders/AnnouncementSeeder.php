<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Announcement::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $announcements = [
            [
                'text' => 'Complimentary Engraving on Orders Above 500 AED',
                'sort_order' => 1,
            ],
            [
                'text' => 'Free Shipping on All Orders Over 300 AED',
                'sort_order' => 2,
            ],
            [
                'text' => 'Discover Our Exclusive Summer Collection',
                'sort_order' => 3,
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::create($announcement);
        }
    }
}
