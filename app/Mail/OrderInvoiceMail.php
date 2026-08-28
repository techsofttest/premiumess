<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing(['items.product.brand', 'items.variant', 'customer']);
    }

    public function build()
    {
        return $this->subject("Order Confirmation & Invoice - #{$this->order->order_number} | Premium Essence")
                    ->html($this->renderHtmlContent());
    }

    private function renderHtmlContent(): string
    {
        $order = $this->order;
        $customerName = $order->customer_name ?: ($order->first_name . ' ' . $order->last_name);
        if (trim($customerName) === '') {
            $customerName = 'Valued Customer';
        }

        $itemsHtml = '';
        foreach ($order->items as $item) {
            $isCuratedDeal = $item->getCuratedDeal() !== null || empty($item->product_id);
            $brandName = (!$isCuratedDeal && $item->product && $item->product->brand) ? $item->product->brand->name : null;

            $vDetail = $item->variant_details;
            if (!$vDetail && $item->variant) {
                $v = $item->variant;
                $vDetail = trim(($v->name ?? '') . ($v->size ? ' (' . $v->size . ($v->unit ? ' ' . $v->unit : '') . ')' : ''));
            }
            $variant = $vDetail ? " <span style='color: #C5A059; font-weight: 600;'>[{$vDetail}]</span>" : '';
            $unitPrice = number_format((float) $item->price, 2);
            $lineTotal = number_format((float) $item->line_total, 2);

            $itemTitleHtml = $brandName
                ? "<strong style='color: #1B1315;'>{$brandName}</strong> - {$item->product_name}{$variant}"
                : "{$item->product_name}{$variant}";

            $itemsHtml .= "
            <tr>
                <td style='padding: 12px 15px; border-bottom: 1px solid #EAEAEA; font-size: 14px; color: #1B1315;'>
                    {$itemTitleHtml}
                </td>
                <td style='padding: 12px 15px; border-bottom: 1px solid #EAEAEA; font-size: 14px; color: #1B1315; text-align: center;'>
                    {$item->quantity}
                </td>
                <td style='padding: 12px 15px; border-bottom: 1px solid #EAEAEA; font-size: 14px; color: #1B1315; text-align: right;'>
                    {$unitPrice} AED
                </td>
                <td style='padding: 12px 15px; border-bottom: 1px solid #EAEAEA; font-size: 14px; color: #1B1315; text-align: right; font-weight: bold;'>
                    {$lineTotal} AED
                </td>
            </tr>
            ";
        }

        $subtotalFormatted = number_format((float) $order->subtotal, 2);
        $discountFormatted = number_format((float) $order->discount, 2);
        $shippingFormatted = number_format((float) $order->shipping_cost, 2);
        $grandTotalFormatted = number_format((float) $order->grand_total, 2);
        $orderDate = $order->created_at ? $order->created_at->format('F j, Y, g:i a') : date('F j, Y');

        $shippingPhone = $order->shipping_phone ?: $order->customer_phone ?: $order->phone ?: 'N/A';
        $shippingAddress = implode(', ', array_filter([
            $order->shipping_address_line_1 ?: $order->address,
            $order->shipping_address_line_2 ?: $order->apartment,
            $order->shipping_city ?: $order->city,
            $order->shipping_state ?: $order->state,
            $order->shipping_country ?: $order->country,
        ]));

        $billingName = $order->billing_name ?: $customerName;
        $billingPhone = $order->billing_phone ?: $shippingPhone;
        $billingAddress = implode(', ', array_filter([
            $order->billing_address_line_1 ?: ($order->shipping_address_line_1 ?: $order->address),
            $order->billing_address_line_2 ?: ($order->shipping_address_line_2 ?: $order->apartment),
            $order->billing_city ?: ($order->shipping_city ?: $order->city),
            $order->billing_state ?: ($order->shipping_state ?: $order->state),
            $order->billing_postcode ?: $order->pin_code,
            $order->billing_country ?: ($order->shipping_country ?: $order->country),
        ]));
        if (trim($billingAddress) === '') {
            $billingAddress = $shippingAddress;
        }

        $logoUrl = asset('images/logo/brand-logo-nobg.png');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Order Invoice - #{$order->order_number}</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #F7F3F4; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; color: #1B1315;'>
            <div style='max-width: 650px; margin: 30px auto; background-color: #FFFFFF; border: 1px solid #EAEAEA; border-radius: 4px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                
                <!-- Header -->
                <div style='background-color: #1B1315; padding: 30px 40px; text-align: center;'>
                    <div style='margin-bottom: 12px;'>
                        <img src='{$logoUrl}' alt='Premium Essence Logo' style='max-height: 60px; width: auto; display: inline-block; vertical-align: middle;' />
                    </div>
                    <h1 style='color: #EAEAEA; margin: 0; font-family: Georgia, serif; font-size: 26px; letter-spacing: 2px; text-transform: uppercase;'>
                        Premium Essence
                    </h1>
                    <p style='color: #EAEAEA; margin: 5px 0 0 0; font-size: 11px; letter-spacing: 3px; text-transform: uppercase;'>
                        Luxury Perfumery LLC &bull; Abu Dhabi, UAE
                    </p>
                </div>

                <!-- Status Banner -->
                <div style='background-color: #F0FDF4; border-bottom: 1px solid #DCFCE7; padding: 15px 40px; text-align: center;'>
                    <span style='color: #166534; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;'>
                        &check; Payment Successful & Order Confirmed
                    </span>
                </div>

                <!-- Body Content -->
                <div style='padding: 40px;'>
                    <p style='font-size: 16px; line-height: 1.5; color: #1B1315; margin-top: 0;'>
                        Dear <strong>{$customerName}</strong>,
                    </p>
                    <p style='font-size: 14px; line-height: 1.6; color: #4A4A4A;'>
                        Thank you for your order with <strong>Premium Essence</strong>. We are pleased to confirm that your payment has been successfully processed and your luxury order is being prepared for dispatch.
                    </p>

                    <!-- Order Info Box -->
                    <table style='width: 100%; margin: 25px 0; background-color: #FAF7F8; border: 1px solid #EAEAEA; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 15px; font-size: 13px; color: #666; border-bottom: 1px solid #EAEAEA; border-right: 1px solid #EAEAEA;'>
                                Order Number:<br>
                                <strong style='font-size: 15px; color: #1B1315;'>#{$order->order_number}</strong>
                            </td>
                            <td style='padding: 15px; font-size: 13px; color: #666; border-bottom: 1px solid #EAEAEA;'>
                                Order Date:<br>
                                <strong style='font-size: 15px; color: #1B1315;'>{$orderDate}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 15px; font-size: 13px; color: #666; border-right: 1px solid #EAEAEA;'>
                                Payment Method:<br>
                                <strong style='font-size: 14px; color: #1B1315; text-transform: uppercase;'>{$order->payment_method} (Stripe Verified)</strong>
                            </td>
                            <td style='padding: 15px; font-size: 13px; color: #666;'>
                                Delivery Method:<br>
                                <strong style='font-size: 14px; color: #1B1315; text-transform: uppercase;'>{$order->delivery_type} Delivery</strong>
                            </td>
                        </tr>
                    </table>

                    <!-- Items Table -->
                    <h3 style='font-family: Georgia, serif; font-size: 18px; color: #1B1315; margin: 30px 0 15px 0; border-bottom: 2px solid #D4AF37; padding-bottom: 8px;'>
                        Order Items
                    </h3>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 25px;'>
                        <thead>
                            <tr style='background-color: #1B1315; color: #FFFFFF;'>
                                <th style='padding: 10px 15px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Item Description</th>
                                <th style='padding: 10px 15px; text-align: center; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Qty</th>
                                <th style='padding: 10px 15px; text-align: right; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Unit Price</th>
                                <th style='padding: 10px 15px; text-align: right; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemsHtml}
                        </tbody>
                    </table>

                    <!-- Financial Summary -->
                    <table style='width: 100%; max-width: 300px; margin-left: auto; border-collapse: collapse; font-size: 14px;'>
                        <tr>
                            <td style='padding: 6px 0; color: #666;'>Subtotal:</td>
                            <td style='padding: 6px 0; text-align: right; color: #1B1315;'>{$subtotalFormatted} AED</td>
                        </tr>
                        " . ($order->discount > 0 ? "
                        <tr>
                            <td style='padding: 6px 0; color: #166534;'>Discount ({$order->coupon_code}):</td>
                            <td style='padding: 6px 0; text-align: right; color: #166534;'>-{$discountFormatted} AED</td>
                        </tr>
                        " : "") . "
                        <tr>
                            <td style='padding: 6px 0; color: #666;'>Shipping Fee:</td>
                            <td style='padding: 6px 0; text-align: right; color: #1B1315;'>{$shippingFormatted} AED</td>
                        </tr>
                        <tr style='border-top: 2px solid #1B1315; font-size: 16px;'>
                            <td style='padding: 12px 0; font-weight: bold; color: #1B1315;'>Grand Total:</td>
                            <td style='padding: 12px 0; text-align: right; font-weight: bold; color: #D4AF37;'>{$grandTotalFormatted} AED</td>
                        </tr>
                    </table>

                    <!-- Shipping & Billing Address Boxes -->
                    <table style='width: 100%; border-collapse: collapse; margin-top: 30px;'>
                        <tr>
                            <td style='width: 48%; vertical-align: top; padding: 15px; background-color: #FAF7F8; border-left: 4px solid #D4AF37;'>
                                <h4 style='margin: 0 0 8px 0; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #1B1315;'>Shipping Address</h4>
                                <p style='margin: 0; font-size: 12px; line-height: 1.5; color: #4A4A4A;'>
                                    <strong>{$customerName}</strong><br>
                                    {$shippingAddress}<br>
                                    Phone: {$shippingPhone}
                                </p>
                            </td>
                            <td style='width: 4%;'></td>
                            <td style='width: 48%; vertical-align: top; padding: 15px; background-color: #FAF7F8; border-left: 4px solid #1B1315;'>
                                <h4 style='margin: 0 0 8px 0; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #1B1315;'>Billing Address</h4>
                                <p style='margin: 0; font-size: 12px; line-height: 1.5; color: #4A4A4A;'>
                                    <strong>{$billingName}</strong><br>
                                    {$billingAddress}<br>
                                    Phone: {$billingPhone}
                                </p>
                            </td>
                        </tr>
                    </table>

                    <p style='font-size: 13px; line-height: 1.6; color: #666; margin-top: 35px;'>
                        You can track your order status anytime by visiting our website using your order number: <strong>#{$order->order_number}</strong>.
                    </p>
                </div>

                <!-- Footer -->
                <div style='background-color: #F7F3F4; padding: 25px 40px; border-top: 1px solid #EAEAEA; text-align: center; font-size: 12px; color: #888; line-height: 1.6;'>
                    <p style='margin: 0;'>
                        <strong>Premium Essence Perfumes LLC</strong><br>
                        Musaffah, M/9, Abu Dhabi, UAE &bull; PO Box: 92282<br>
                        Email: <a href='mailto:sales@premium-perfumes.com' style='color: #1B1315;'>sales@premium-perfumes.com</a> &bull; Tel: +971 55 723 2010
                    </p>
                    <p style='margin: 15px 0 0 0; color: #AAA;'>
                        &copy; " . date('Y') . " Premium Essence Perfumes LLC. All rights reserved.
                    </p>
                </div>

            </div>
        </body>
        </html>
        ";
    }
}
