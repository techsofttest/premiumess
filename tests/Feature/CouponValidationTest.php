<?php

namespace Tests\Feature;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_validation_endpoint_accepts_valid_code(): void
    {
        Coupon::create([
            'coupon_code' => 'SAVE10',
            'coupon_type' => 1,
            'coupon_amount' => 10,
            'coupon_fromdate' => now()->subDay()->toDateString(),
            'coupon_todate' => now()->addDay()->toDateString(),
        ]);

        $response = $this->postJson('/api/coupons/validate', [
            'coupon_code' => 'SAVE10',
            'subtotal' => 100,
        ]);

        $response->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('coupon.coupon_code', 'SAVE10');
    }

    public function test_coupon_validation_endpoint_rejects_invalid_code(): void
    {
        $response = $this->postJson('/api/coupons/validate', [
            'coupon_code' => 'NOTREAL',
            'subtotal' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('valid', false);
    }
}
