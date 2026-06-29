<?php

use App\Models\ProgrammingLanguage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (ProgrammingLanguage::where('slug', 'dart')->exists()) {
            return;
        }

        $flutterOrder = ProgrammingLanguage::where('slug', 'flutter')->value('sort_order');
        $insertOrder = $flutterOrder !== null ? ((int) $flutterOrder + 1) : 17;

        ProgrammingLanguage::where('sort_order', '>=', $insertOrder)->increment('sort_order');

        ProgrammingLanguage::create([
            'name' => 'Dart',
            'slug' => 'dart',
            'display_name' => 'Dart',
            'description' => 'لغة برمجة Flutter لتطبيقات الموبايل',
            'category' => 'mobile',
            'icon' => 'qb-lang-icon--dart',
            'color' => '#0175C2',
            'is_active' => true,
            'sort_order' => $insertOrder,
        ]);
    }

    public function down(): void
    {
        $dart = ProgrammingLanguage::where('slug', 'dart')->first();

        if (! $dart) {
            return;
        }

        $order = (int) $dart->sort_order;
        $dart->delete();

        ProgrammingLanguage::where('sort_order', '>', $order)->decrement('sort_order');
    }
};
