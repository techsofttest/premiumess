<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaultPages = [
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms & Conditions',
                'meta_title' => 'Terms & Conditions - Premium Essence Perfumes LLC',
                'description' => 'Terms of service, sales policy, website use agreement, and legal conditions of Premium Essence Perfumes LLC.',
                'content' => '
                    <h2>1. Agreement to Terms</h2>
                    <p>Welcome to Premium Essence Perfumes LLC ("Company", "we", "our", "us"). By accessing or using our website, purchasing our luxury fragrances, or engaging with our services, you agree to be bound by these Terms & Conditions. Please read them carefully before completing any transaction.</p>
                    
                    <h2>2. Intellectual Property Rights</h2>
                    <p>All content presented on this platform—including brand logos, images, perfume descriptions, artistic assets, typography, and website design—is the exclusive intellectual property of Premium Essence Perfumes LLC and its licensed perfume houses. Unauthorized reproduction or commercial distribution is strictly prohibited.</p>

                    <h2>3. Product Information & Pricing</h2>
                    <p>We take meticulous care to display accurate pricing (in UAE Dirhams - AED), ingredient breakdowns, olfactive families, and product descriptions. Prices and promotional offers are subject to change without prior notice. All orders are processed subject to stock availability and verification.</p>

                    <h2>4. Orders & Payment Processing</h2>
                    <p>Payments are securely processed via Stripe or authorized gateway providers. By placing an order, you warrant that you are authorized to use the chosen payment instrument. An order confirmation email and invoice will be automatically dispatched to your registered email upon payment verification.</p>

                    <h2>5. Governing Law & Jurisdiction</h2>
                    <p>These terms shall be governed and interpreted in accordance with the federal laws of the United Arab Emirates and the local laws applicable in the Emirate of Abu Dhabi. Any dispute arising under these terms shall fall under the exclusive jurisdiction of Abu Dhabi courts.</p>
                ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'meta_title' => 'Privacy Policy - Premium Essence Perfumes LLC',
                'description' => 'Data privacy protection, personal data collection, cookies policy, and customer security guidelines of Premium Essence Perfumes LLC.',
                'content' => '
                    <h2>1. Information We Collect</h2>
                    <p>Premium Essence Perfumes LLC respects your personal privacy. We collect essential personal data when you create an account, place an order, or subscribe to our newsletter—including your full name, email address, delivery phone number, shipping address, and order transaction history.</p>

                    <h2>2. How We Use Your Personal Data</h2>
                    <p>Your information is used strictly to fulfill order purchases, send automated tracking updates and invoices, provide concierge customer service, and (with your explicit consent) send exclusive invitations to private perfume releases.</p>

                    <h2>3. Payment Security & Encryption</h2>
                    <p>We do NOT store credit card numbers or sensitive financial instruments on our servers. All online card transactions are processed using high-grade SSL encryption handled directly by Stripe (PCI-DSS Level 1 certified gateway).</p>

                    <h2>4. Cookies & Analytics</h2>
                    <p>Our website utilizes cookies to remember your cart selection, store active session tokens, and optimize your browsing experience. You may configure your browser settings to decline cookies at any time.</p>

                    <h2>5. Contacting Our Data Privacy Officer</h2>
                    <p>If you wish to review, correct, or delete any personal information associated with your account, please contact our Privacy Team at <strong>sales@premium-perfumes.com</strong> or call us at <strong>+971 55 723 2010</strong>.</p>
                ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'refund-and-return',
                'title' => 'Refund & Return Policy',
                'meta_title' => 'Refund & Return Policy - Premium Essence Perfumes LLC',
                'description' => 'Returns policy, exchange process, damaged goods claim, and refund terms of Premium Essence Perfumes LLC.',
                'content' => '
                    <h2>1. Returns & Exchange Period</h2>
                    <p>At Premium Essence, we take immense pride in delivering original, authentic luxury fragrances. We offer a <strong>14-Day Return & Exchange Window</strong> from the date of package receipt for eligible, unopened items.</p>

                    <h2>2. Eligibility Criteria for Returns</h2>
                    <p>To qualify for a full refund or exchange, items must satisfy the following conditions:</p>
                    <ul>
                        <li>The perfume bottle must remain completely unopened, in its original shrink-wrapped cellophane packaging.</li>
                        <li>All security seals, batch tags, and luxury gift boxes must be intact.</li>
                        <li>Proof of purchase (Order Invoice Number) must accompany the return request.</li>
                    </ul>

                    <h2>3. Non-Returnable Items</h2>
                    <p>Due to health and hygiene standards governing personal luxury perfumery, opened perfume bottles, sample discovery vials, and custom-engraved flacons cannot be returned or refunded once opened.</p>

                    <h2>4. Damaged or Faulty Items</h2>
                    <p>If your package arrives damaged during transit or exhibits a defective atomizer nozzle, please notify us within <strong>48 hours</strong> of receipt by emailing <strong>sales@premium-perfumes.com</strong> with photos of the damaged item. We will arrange immediate courier pickup and dispatch a replacement at zero cost.</p>

                    <h2>5. Refund Process & Timelines</h2>
                    <p>Once your returned item is inspected at our Musaffah M/9 Abu Dhabi warehouse, refunds will be credited back to your original payment method within 5 to 7 business days.</p>
                ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'shipping-policy',
                'title' => 'Shipping & Delivery Policy',
                'meta_title' => 'Shipping & Delivery Policy - Premium Essence Perfumes LLC',
                'description' => 'Express delivery rates, courier schedules, direct driver fulfillment in Abu Dhabi & UAE nationwide shipping details.',
                'content' => '
                    <h2>1. UAE Nationwide Express Delivery</h2>
                    <p>We deliver luxury fragrances across all seven Emirates (Abu Dhabi, Dubai, Sharjah, Ajman, Ras Al Khaimah, Fujairah, and Umm Al Quwain) via our private direct driver fleet and premium express couriers.</p>

                    <h2>2. Shipping Rates & Free Delivery</h2>
                    <ul>
                        <li><strong>Free Standard Delivery:</strong> On all orders above 250 AED across UAE.</li>
                        <li><strong>Standard Courier Fee:</strong> Flat rate of 20 AED for orders under 250 AED.</li>
                        <li><strong>Direct Same-Day Delivery (Abu Dhabi / Musaffah):</strong> Available for select postcodes when ordered before 2:00 PM.</li>
                    </ul>

                    <h2>3. Dispatch & Delivery Timelines</h2>
                    <p>Orders placed before 2:00 PM GST are processed and dispatched on the same business day. Delivery times are generally 24 to 48 hours for urban UAE cities.</p>

                    <h2>4. Package Inspection & Signature</h2>
                    <p>Every shipment is packaged in temperature-regulated, shockproof luxury boxes to ensure scent stability. A signature or OTP confirmation upon receipt is required to secure delivery.</p>

                    <h2>5. Order Tracking</h2>
                    <p>You can track your package in real-time by visiting our <a href="/track-order">Order Tracking Page</a> using your Order Number and registered Email/Phone number.</p>
                ',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($defaultPages as $page) {
            DB::table('cms')->updateOrInsert(
                ['slug' => $page['slug']],
                $page
            );
        }
    }

    public function down(): void
    {
        //
    }
};
