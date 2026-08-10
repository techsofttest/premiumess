<?php

namespace App\Enums;

enum TaskType: string
{
    case ORDER_PACKING = 'order_packing';
    case DELIVERY = 'delivery';
    case INVENTORY_COUNT = 'inventory_count';
    case CUSTOMER_SUPPORT = 'customer_support';
    case GENERAL_MAINTENANCE = 'general_maintenance';
    case QUALITY_CHECK = 'quality_check';
    case STOCK_ORGANIZATION = 'stock_organization';
    case RETURNS_PROCESSING = 'returns_processing';

    public function getLabel(): string
    {
        return match ($this) {
            self::ORDER_PACKING => 'Order Packing',
            self::DELIVERY => 'Delivery',
            self::INVENTORY_COUNT => 'Inventory Count',
            self::CUSTOMER_SUPPORT => 'Customer Support',
            self::GENERAL_MAINTENANCE => 'General Maintenance',
            self::QUALITY_CHECK => 'Quality Check',
            self::STOCK_ORGANIZATION => 'Stock Organization',
            self::RETURNS_PROCESSING => 'Returns Processing',
        };
    }
}
