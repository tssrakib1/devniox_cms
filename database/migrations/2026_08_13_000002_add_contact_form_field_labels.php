<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_contact_content', function (Blueprint $table) {
            $table->string('form_name_label', 120)->nullable()->after('form_description');
            $table->string('form_company_label', 120)->nullable()->after('form_name_label');
            $table->string('form_email_label', 120)->nullable()->after('form_company_label');
            $table->string('form_phone_label', 120)->nullable()->after('form_email_label');
            $table->string('optional_label', 80)->nullable()->after('form_phone_label');
        });

        DB::table('cms_contact_content')->update([
            'form_name_label' => 'Name',
            'form_company_label' => 'Company',
            'form_email_label' => 'Email',
            'form_phone_label' => 'Phone',
            'optional_label' => 'optional',
        ]);
    }

    public function down(): void
    {
        Schema::table('cms_contact_content', function (Blueprint $table) {
            $table->dropColumn(['form_name_label', 'form_company_label', 'form_email_label', 'form_phone_label', 'optional_label']);
        });
    }
};
