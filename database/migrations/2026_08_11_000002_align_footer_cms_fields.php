<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'company_heading' => fn (Blueprint $table) => $table->string('company_heading', 100)->nullable()->after('short_description'),
            'resources_heading' => fn (Blueprint $table) => $table->string('resources_heading', 100)->nullable()->after('company_heading'),
            'conversation_heading' => fn (Blueprint $table) => $table->string('conversation_heading', 100)->nullable()->after('resources_heading'),
            'conversation_description' => fn (Blueprint $table) => $table->text('conversation_description')->nullable()->after('conversation_heading'),
            'contact_email' => fn (Blueprint $table) => $table->string('contact_email', 254)->nullable()->after('conversation_description'),
            'contact_phone' => fn (Blueprint $table) => $table->string('contact_phone', 50)->nullable()->after('contact_email'),
            'bottom_right_text' => fn (Blueprint $table) => $table->string('bottom_right_text', 255)->nullable()->after('contact_phone'),
        ];

        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn('cms_footer_content', $column)) {
                Schema::table('cms_footer_content', $definition);
            }
        }

        $footer = \DB::table('cms_footer_content')->first();
        if ($footer) {
            \DB::table('cms_footer_content')->where('id', $footer->id)->update([
                'company_heading' => $footer->quick_links_heading,
                'resources_heading' => $footer->products_heading,
                'conversation_heading' => $footer->services_heading,
                'conversation_description' => $footer->short_description,
                'contact_email' => config('mail.from.address'),
                'bottom_right_text' => 'Software products - Enterprise systems - Business automation',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('cms_footer_content', function (Blueprint $table) {
            $table->dropColumn([
                'company_heading',
                'resources_heading',
                'conversation_heading',
                'conversation_description',
                'contact_email',
                'contact_phone',
                'bottom_right_text',
            ]);
        });
    }
};
