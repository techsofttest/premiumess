<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $journals = [
            [
                'title' => 'The Art of Layering Niche Fragrances',
                'slug' => 'the-art-of-layering-niche-fragrances',
                'category' => 'Haute Parfumerie',
                'author' => 'Jean-Luc François',
                'excerpt' => 'Discover the secret techniques of European master perfumers to create a signature olfactive aura through intelligent accords.',
                'content' => '
                    <p class="lead">Fragrance layering is an intimate art form—a personal ritual of combining distinct notes to craft a bespoke aura that belongs uniquely to you.</p>
                    <h3>Understanding Base Accords</h3>
                    <p>When curating a layered fragrance, always begin with a rich, resonant base note. Woody accords, aged ambergris, and Royal Oud provide a warm foundation that grounds lighter top notes.</p>
                    <p>Apply your heaviest concentration first—such as an Extrait de Parfum or concentrated attar—directly onto pulse points: the wrists, inner elbows, and nape of the neck.</p>
                    <h3>Harmonizing Contrast Notes</h3>
                    <p>Allow the base accord 30 seconds to settle before layering a complementary heart note. A crisp Calabrian Bergamot or Damask Rose infused Eau de Parfum over a smoky oud base creates an enchanting interplay between freshness and oriental depth.</p>
                    <blockquote>"A truly great perfume does not simply coat the skin; it converses with your body’s natural warmth to produce an unrepeatable signature."</blockquote>
                ',
                'image' => 'journals/layering.jpg',
                'is_published' => true,
                'published_at' => now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Oud & Amber: A History of Royal Arabian Perfumery',
                'slug' => 'oud-and-amber-history-of-royal-arabian-perfumery',
                'category' => 'Heritage & Notes',
                'author' => 'Sheikh Hamdan Al-Maktoum',
                'excerpt' => 'Trace the centuries-old legacy of Agarwood harvesting across East Asia and its sacred role in Arabian court traditions.',
                'content' => '
                    <p>For millennia, Agarwood—reverently known as liquid gold or Oud—has occupied the pinnacle of royal perfumery. Extracted from the resinous heartwood of infected Aquilaria trees, pure Oud resin commands prices exceeding fine gold.</p>
                    <h3>The Sacred Ritual of Bakhoor & Dehn Al Oud</h3>
                    <p>In Arabian hospitality, burning rare chips of wild Assam Oud over glowing charcoal coals signifies the highest honor accorded to an honored guest. The fragrant smoke lingers in garments, infusing silk and wool with an unmistakable regal majesty.</p>
                    <h3>The Science of Aging Oud</h3>
                    <p>Like fine vintage reserves, aged Dehn Al Oud deepens in complexity over decades. Storage in dark crystal flagons transforms sharp medicinal top notes into velvety animalic, honeyed, and balsamic accords.</p>
                ',
                'image' => 'journals/oud-history.jpg',
                'is_published' => true,
                'published_at' => now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'How to Choose Your Signature Fragrance for Every Season',
                'slug' => 'how-to-choose-your-signature-fragrance-for-every-season',
                'category' => 'Olfactory Guides',
                'author' => 'Elena Rostova',
                'excerpt' => 'Temperature, humidity, and skin chemistry fundamentally alter scent diffusion. Here is how to navigate the wheel of seasons.',
                'content' => '
                    <p>As ambient temperatures shift throughout the year, the volatility of essential perfume oils changes dramatically. A fragrance that sings beautifully in winter may feel overwhelming under summer heat.</p>
                    <h3>Spring: Verdant Florals & Sparkling Citrus</h3>
                    <p>Spring calls for crisp renewal. Look for olfactive families featuring Neroli, Green Tea, Lily of the Valley, and Pink Pepper. These bright molecules evaporate cleanly without feeling heavy.</p>
                    <h3>Summer: Aquatic & Mineral Accords</h3>
                    <p>High humidity accelerates evaporation. Opt for fresh marine accords, Vetiver, and Italian Sea Salt notes that stay vibrant under sunlit weather.</p>
                    <h3>Autumn & Winter: Gourmands, Spices & Resins</h3>
                    <p>Cooler air contracts scent projection. Embrace rich vanilla, tonka bean, saffron, cardamom, and leathery woods that envelope the senses in cozy warmth.</p>
                ',
                'image' => 'journals/signature-scent.jpg',
                'is_published' => true,
                'published_at' => now()->subDays(8),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($journals as $j) {
            DB::table('journals')->updateOrInsert(
                ['slug' => $j['slug']],
                $j
            );
        }
    }

    public function down(): void
    {
        //
    }
};
