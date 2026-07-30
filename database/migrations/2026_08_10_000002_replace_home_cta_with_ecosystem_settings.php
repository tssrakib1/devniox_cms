<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_home_content', function (Blueprint $table) {
            if (! Schema::hasColumn('cms_home_content', 'ecosystem_enabled')) {
                $table->boolean('ecosystem_enabled')->default(true)->after('articles_description');
            }
            if (! Schema::hasColumn('cms_home_content', 'ecosystem_label')) {
                $table->string('ecosystem_label', 80)->default('OUR ECOSYSTEM')->after('ecosystem_enabled');
            }
            if (! Schema::hasColumn('cms_home_content', 'ecosystem_title')) {
                $table->string('ecosystem_title', 180)->default('Powerful Platforms. One Parent Company.')->after('ecosystem_label');
            }
            if (! Schema::hasColumn('cms_home_content', 'ecosystem_description')) {
                $table->text('ecosystem_description')->nullable()->after('ecosystem_title');
            }
            if (! Schema::hasColumn('cms_home_content', 'ecosystem_note')) {
                $table->text('ecosystem_note')->nullable()->after('ecosystem_description');
            }
        });

        DB::table('cms_home_content')->update([
            'ecosystem_enabled' => true,
            'ecosystem_label' => 'OUR ECOSYSTEM',
            'ecosystem_title' => 'Powerful Platforms. One Parent Company.',
            'ecosystem_description' => 'Ravoltify Technologies builds and manages a growing ecosystem of software products and digital platforms designed to help businesses operate more efficiently.',
            'ecosystem_note' => 'All platforms are developed, maintained and supported by Ravoltify Technologies.',
        ]);

        $obsolete = array_values(array_filter(['cta_heading', 'cta_description', 'cta_button_text', 'cta_button_url', 'cta_background_path'], fn (string $column): bool => Schema::hasColumn('cms_home_content', $column)));
        if ($obsolete !== []) {
            Schema::table('cms_home_content', fn (Blueprint $table) => $table->dropColumn($obsolete));
        }
    }

    public function down(): void
    {
        Schema::table('cms_home_content', function (Blueprint $table) {
            if (! Schema::hasColumn('cms_home_content', 'cta_heading')) {
                $table->string('cta_heading', 180)->default('Build your next advantage');
            }
            if (! Schema::hasColumn('cms_home_content', 'cta_description')) {
                $table->text('cta_description')->nullable();
            }
            if (! Schema::hasColumn('cms_home_content', 'cta_button_text')) {
                $table->string('cta_button_text', 80)->default('Contact us');
            }
            if (! Schema::hasColumn('cms_home_content', 'cta_button_url')) {
                $table->string('cta_button_url', 2048)->default('/contact');
            }
            if (! Schema::hasColumn('cms_home_content', 'cta_background_path')) {
                $table->string('cta_background_path')->nullable();
            }
        });

        $ecosystem = array_values(array_filter(['ecosystem_enabled', 'ecosystem_label', 'ecosystem_title', 'ecosystem_description', 'ecosystem_note'], fn (string $column): bool => Schema::hasColumn('cms_home_content', $column)));
        if ($ecosystem !== []) {
            Schema::table('cms_home_content', fn (Blueprint $table) => $table->dropColumn($ecosystem));
        }
    }
};
