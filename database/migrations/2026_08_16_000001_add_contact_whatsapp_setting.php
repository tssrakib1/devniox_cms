<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['group' => 'contact', 'key' => 'whatsapp'],
            ['value' => '', 'type' => 'string', 'is_public' => true]
        );
    }

    public function down(): void
    {
        Setting::where(['group' => 'contact', 'key' => 'whatsapp'])->delete();
    }
};
