<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminOrderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing(['items.product.brand', 'items.variant', 'customer']);
    }

    public function build()
    {
        $totalFormatted = number_format((float) $this->order->grand_total, 2);
        return $this->subject("NEW PAID ORDER: #{$this->order->order_number} ({$totalFormatted} AED) | Premium Essence Admin")
                    ->html($this->renderHtmlContent());
    }

    private function renderHtmlContent(): string
    {
        $order = $this->order;
        $customerName = $order->customer_name ?: ($order->first_name . ' ' . $order->last_name);
        if (trim($customerName) === '') {
            $customerName = 'Guest Customer';
        }
        $customerEmail = $order->customer_email ?: $order->email ?: 'N/A';
        $customerPhone = $order->shipping_phone ?: $order->customer_phone ?: $order->phone ?: 'N/A';

        $itemsHtml = '';
        foreach ($order->items as $item) {
            $brandName = $item->product && $item->product->brand ? $item->product->brand->name : 'Premium Essence';
            $vDetail = $item->variant_details;
            if (!$vDetail && $item->variant) {
                $v = $item->variant;
                $vDetail = trim(($v->name ?? '') . ($v->size ? ' (' . $v->size . ($v->unit ? ' ' . $v->unit : '') . ')' : ''));
            }
            $variant = $vDetail ? " <span style='color: #D4AF37; font-weight: bold;'>[{$vDetail}]</span>" : '';
            $unitPrice = number_format((float) $item->price, 2);
            $lineTotal = number_format((float) $item->line_total, 2);

            $itemsHtml .= "
            <tr>
                <td style='padding: 10px 12px; border-bottom: 1px solid #EAEAEA; font-size: 13px;'>
                    <strong>{$brandName}</strong> - {$item->product_name}{$variant}
                </td>
                <td style='padding: 10px 12px; border-bottom: 1px solid #EAEAEA; font-size: 13px; text-align: center;'>
                    {$item->quantity}
                </td>
                <td style='padding: 10px 12px; border-bottom: 1px solid #EAEAEA; font-size: 13px; text-align: right;'>
                    {$unitPrice} AED
                </td>
                <td style='padding: 10px 12px; border-bottom: 1px solid #EAEAEA; font-size: 13px; text-align: right; font-weight: bold;'>
                    {$lineTotal} AED
                </td>
            </tr>
            ";
        }

        $grandTotalFormatted = number_format((float) $order->grand_total, 2);
        $orderDate = $order->created_at ? $order->created_at->format('F j, Y, g:i a') : date('F j, Y');

        $shippingAddress = implode(', ', array_filter([
            $order->shipping_address_line_1 ?: $order->address,
            $order->shipping_address_line_2 ?: $order->apartment,
            $order->shipping_city ?: $order->city,
            $order->shipping_state ?: $order->state,
            $order->shipping_country ?: $order->country,
        ]));

        $logoUrl = asset('images/logo/brand-logo-nobg.png');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Admin Order Notification - #{$order->order_number}</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #F7F3F4; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; color: #1B1315;'>
            <div style='max-width: 650px; margin: 30px auto; background-color: #FFFFFF; border: 1px solid #EAEAEA; border-radius: 4px; overflow: hidden;'>
                
                <div style='background-color: #1B1315; padding: 25px 35px; color: #FFFFFF;'>
                    <div style='margin-bottom: 10px;'>
                        <img src='{$logoUrl}' alt='Premium Essence Logo' style='max-height: 45px; width: auto; display: inline-block; vertical-align: middle;' />
                    </div>
                    <span style='background-color: #D4AF37; color: #1B1315; font-size: 10px; font-weight: bold; padding: 4px 8px; text-transform: uppercase; letter-spacing: 1.5px; border-radius: 2px; float: right;'>NEW ORDER</span>
                    <h2 style='margin: 0; font-family: Georgia, serif; font-size: 22px; color: #D4AF37;'>
                        Order #{$order->order_number}
                    </h2>
                    <p style='margin: 5px 0 0 0; font-size: 12px; color: #CCCCCC;'>
                        Received on {$orderDate}
                    </p>
                </div>

                <div style='padding: 35px;'>
                    <h3 style='margin-top: 0; font-size: 16px; color: #1B1315; border-bottom: 1px solid #EAEAEA; pb: 8px;'>Customer & Delivery Information</h3>
                    <table style='width: 100%; margin-bottom: 25px; font-size: 13px; color: #333;'>
                        <tr>
                            <td style='padding: 6px 0; width: 35%; color: #666;'>Customer Name:</td>
                            <td style='padding: 6px 0; font-weight: bold;'>{$customerName}</td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #666;'>Customer Email:</td>
                            <td style='padding: 6px 0;'><a href='mailto:{$customerEmail}'>{$customerEmail}</a></td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #666;'>Customer Phone:</td>
                            <td style='padding: 6px 0; font-weight: bold;'>{$customerPhone}</td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #666;'>Delivery Address:</td>
                            <td style='padding: 6px 0;'>{$shippingAddress}</td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #666;'>Delivery Type:</td>
                            <td style='padding: 6px 0; font-weight: bold; text-transform: uppercase;'>{$order->delivery_type}</td>
                        </tr>
                    </table>

                    <h3 style='font-size: 16px; color: #1B1315; border-bottom: 1px solid #EAEAEA; pb: 8px;'>Purchased Items</h3>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                        <thead>
                            <tr style='background-color: #F7F3F4;'>
                                <th style='padding: 8px 12px; text-align: left; font-size: 11px; text-transform: uppercase;'>Product</th>
                                <th style='padding: 8px 12px; text-align: center; font-size: 11px; text-transform: uppercase;'>Qty</th>
                                <th style='padding: 8px 12px; text-align: right; font-size: 11px; text-transform: uppercase;'>Price</th>
                                <th style='padding: 8px 12px; text-align: right; font-size: 11px; text-transform: uppercase;'>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemsHtml}
                        </tbody>
                    </table>

                    <div style='padding: 15px; background-color: #F0FDF4; border: 1px solid #DCFCE7; text-align: right; font-size: 16px; font-weight: bold; color: #166534;'>
                        Total Amount Paid: {$grandTotalFormatted} AED
                    </div>
                </div>

                <div style='background-color: #F7F3F4; padding: 20px 35px; border-top: 1px solid #EAEAEA; text-align: center;'>
                    <p style='margin: 0; font-size: 12px; color: #666;'>
                        Please access your admin panel to process and dispatch this order.
                    </p>
                </div>

            </div>
        </body>
        </html>
        ";
    }
}
