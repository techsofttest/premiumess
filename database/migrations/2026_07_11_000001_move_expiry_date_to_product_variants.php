<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $prefix = Schema::getConnection()->getTablePrefix();
        $variantsTable = $prefix . 'product_variants';
        $productsTable = $prefix . 'products';

        if (Schema::hasTable($variantsTable) && !Schema::hasColumn($variantsTable, 'expiry_date')) {
            Schema::table($variantsTable, function (Blueprint $table) {
                $table->date('expiry_date')->nullable()->after('tax_percentage');
            });
        }

        if (Schema::hasTable($productsTable) && Schema::hasTable($variantsTable) && Schema::hasColumn($productsTable, 'expiry_date')) {
            DB::statement(
                sprintf(
                    'UPDATE %s
                    SET expiry_date = (
                        SELECT %s.expiry_date
                        FROM %s
                        WHERE %s.id = %s.product_id
                    )
                    WHERE %s.expiry_date IS NULL
                      AND EXISTS (
                          SELECT 1
                          FROM %s
                          WHERE %s.id = %s.product_id
                            AND %s.expiry_date IS NOT NULL
                      )',
                    $variantsTable,
                    $productsTable,
                    $productsTable,
                    $productsTable,
                    $variantsTable,
                    $variantsTable,
                    $productsTable,
                    $productsTable,
                    $variantsTable,
                    $productsTable
                )
            );

            Schema::table($productsTable, function (Blueprint $table) {
                $table->dropColumn('expiry_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = Schema::getConnection()->getTablePrefix();
        $variantsTable = $prefix . 'product_variants';
        $productsTable = $prefix . 'products';

        if (Schema::hasTable($productsTable) && !Schema::hasColumn($productsTable, 'expiry_date')) {
            Schema::table($productsTable, function (Blueprint $table) {
                $table->date('expiry_date')->nullable()->after('description');
            });
        }

        if (Schema::hasTable($productsTable) && Schema::hasTable($variantsTable)) {
            DB::statement(
                sprintf(
                    'UPDATE %s
                    SET expiry_date = (
                        SELECT %s.expiry_date
                        FROM %s
                        WHERE %s.product_id = %s.id
                        ORDER BY %s.id
                        LIMIT 1
                    )
                    WHERE %s.expiry_date IS NULL
                      AND EXISTS (
                          SELECT 1
                          FROM %s
                          WHERE %s.product_id = %s.id
                            AND %s.expiry_date IS NOT NULL
                      )',
                    $productsTable,
                    $variantsTable,
                    $variantsTable,
                    $variantsTable,
                    $productsTable,
                    $variantsTable,
                    $productsTable,
                    $variantsTable,
                    $variantsTable,
                    $productsTable,
                    $variantsTable
                )
            );
        }

        if (Schema::hasTable($variantsTable) && Schema::hasColumn($variantsTable, 'expiry_date')) {
            Schema::table($variantsTable, function (Blueprint $table) {
                $table->dropColumn('expiry_date');
            });
        }
    }
};
