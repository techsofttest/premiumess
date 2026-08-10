<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Sarah Al Mansoori',
                'role' => 'Verified Client, Abu Dhabi',
                'quote' => 'Premium Essence has completely redefined my standard for luxury. The authenticity of their Tom Ford collection is impeccable, and the packaging was an experience in itself.',
                'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'James Sterling',
                'role' => 'Verified Client, Dubai',
                'quote' => 'Finding genuine Creed fragrances can be daunting, but their team provided exceptional guidance. The complimentary engraving added a remarkably personal touch to my gift.',
                'image' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=200&auto=format&fit=crop',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Elena Rostova',
                'role' => 'Fragrance Collector',
                'quote' => 'An absolute masterclass in e-commerce elegance. From the curated selection of Maison Francis Kurkdjian to the seamless delivery, every detail breathes excellence.',
                'image' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?q=80&w=200&auto=format&fit=crop',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Amina Al Qasimi',
                'role' => 'Connoisseur, Sharjah',
                'quote' => 'I\'ve ordered multiple custom discovery sets and the presentation is outstanding. It feels like unboxing a piece of pure art.',
                'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=200&auto=format&fit=crop',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Yousef Al Maktoum',
                'role' => 'Premium Collector, Dubai',
                'quote' => 'The Signature Oud Collection is legendary. The depth and longevity of these scents are unmatched in any boutique I\'ve visited in Europe.',
                'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=200&auto=format&fit=crop',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($testimonials as $data) {
            Testimonial::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
