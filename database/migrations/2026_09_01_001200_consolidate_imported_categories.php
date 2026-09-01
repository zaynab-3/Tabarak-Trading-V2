<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryMap = [
            'Candy & Chocolate' => 'Chocolate',
            'Candy Bar' => 'Candy',
            'Cookies & Biscuits' => 'Biscuits',
            'Candy & Sweets' => 'Candy',
            'Confectionery' => 'Candy',
            'Candy & Confectionery' => 'Candy',
            'Wafer Sticks' => 'Biscuits',
            'Chocolate & Confectionery' => 'Chocolate',
            'Snacks & Sweets' => 'Snacks',
            'Cookies' => 'Biscuits',
            'Energy Drink' => 'Beverages',
            'Soft Drink' => 'Beverages',
            'Dairy & Milk Alternatives' => 'Beverages',
            'Soft Drinks' => 'Beverages',
        ];

        DB::transaction(function () use ($categoryMap): void {
            foreach ($categoryMap as $legacyName => $canonicalName) {
                $legacyId = DB::table('categories')->where('name', $legacyName)->value('id');
                $canonicalId = DB::table('categories')->where('name', $canonicalName)->value('id');

                if (! $legacyId || ! $canonicalId) {
                    continue;
                }

                DB::table('products')->where('category_id', $legacyId)->update(['category_id' => $canonicalId]);
                DB::table('import_items')->where('suggested_category', $legacyName)->update(['suggested_category' => $canonicalName]);
                DB::table('categories')->where('id', $legacyId)->delete();
            }

            $chocolateId = DB::table('categories')->where('name', 'Chocolate')->value('id');
            if ($chocolateId) {
                DB::table('products')
                    ->whereIn('name', ['Ferrero Rocher Fine Hazelnut Chocolates'])
                    ->update(['category_id' => $chocolateId]);
                DB::table('import_items')
                    ->whereIn('suggested_name', ['Ferrero Rocher Fine Hazelnut Chocolates'])
                    ->update(['suggested_category' => 'Chocolate']);
            }
        });
    }

    public function down(): void
    {
        // Consolidated catalogue data intentionally remains on the canonical categories.
    }
};
