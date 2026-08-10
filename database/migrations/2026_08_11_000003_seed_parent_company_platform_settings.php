<?php

use App\Enums\SettingGroup;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['parent_company_name', 'Ravoltify Technologies'], ['parent_company_badge', 'Parent Company'],
            ['parent_company_description', 'Ravoltify Technologies builds and manages a growing ecosystem of software products and digital platforms designed to help businesses operate more efficiently.'],
            ['parent_company_logo', null], ['parent_highlight_1_title', 'Strong Foundation'], ['parent_highlight_1_description', 'Innovation and technology excellence.'],
            ['parent_highlight_2_title', 'Ecosystem of Brands'], ['parent_highlight_2_description', 'Specialized solutions for real business challenges.'],
            ['parent_highlight_3_title', 'Unified Vision'], ['parent_highlight_3_description', 'One vision, multiple connected solutions.'],
        ];
        foreach ($defaults as [$key, $value]) {
            Setting::firstOrCreate(['group' => SettingGroup::Company, 'key' => $key], ['value' => $value, 'type' => $key === 'parent_company_logo' ? 'image' : 'text', 'is_public' => true]);
        }
    }

    public function down(): void
    {
        Setting::where('group', SettingGroup::Company)->whereIn('key', ['parent_company_name','parent_company_badge','parent_company_description','parent_company_logo','parent_highlight_1_title','parent_highlight_1_description','parent_highlight_2_title','parent_highlight_2_description','parent_highlight_3_title','parent_highlight_3_description'])->delete();
    }
};
