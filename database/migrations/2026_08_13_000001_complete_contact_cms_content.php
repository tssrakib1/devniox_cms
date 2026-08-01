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
            $table->string('hero_label', 120)->nullable()->after('hero_banner_path');
            $table->string('response_primary_cta_text', 120)->nullable()->after('auto_reply_message');
            $table->string('response_secondary_cta_text', 120)->nullable()->after('response_primary_cta_text');
            $table->string('email_card_label', 120)->nullable()->after('response_secondary_cta_text');
            $table->string('phone_card_label', 120)->nullable()->after('email_card_label');
            $table->string('whatsapp_card_label', 120)->nullable()->after('phone_card_label');
            $table->string('inquiry_card_label', 120)->nullable()->after('whatsapp_card_label');
            $table->string('guidance_label', 120)->nullable()->after('inquiry_card_label');
            $table->string('guidance_title', 180)->nullable()->after('guidance_label');
            $table->text('guidance_description')->nullable()->after('guidance_title');
            $table->string('guidance_one_title', 180)->nullable()->after('guidance_description');
            $table->text('guidance_one_description')->nullable()->after('guidance_one_title');
            $table->string('guidance_one_helper_title', 180)->nullable()->after('guidance_one_description');
            $table->text('guidance_one_helper_items')->nullable()->after('guidance_one_helper_title');
            $table->string('guidance_two_title', 180)->nullable()->after('guidance_one_helper_items');
            $table->text('guidance_two_description')->nullable()->after('guidance_two_title');
            $table->string('guidance_two_helper_title', 180)->nullable()->after('guidance_two_description');
            $table->text('guidance_two_helper_items')->nullable()->after('guidance_two_helper_title');
            $table->string('guidance_two_link_text', 120)->nullable()->after('guidance_two_helper_items');
            $table->string('guidance_three_title', 180)->nullable()->after('guidance_two_link_text');
            $table->text('guidance_three_description')->nullable()->after('guidance_three_title');
            $table->string('guidance_three_helper_title', 180)->nullable()->after('guidance_three_description');
            $table->text('guidance_three_helper_items')->nullable()->after('guidance_three_helper_title');
            $table->string('guidance_three_link_text', 120)->nullable()->after('guidance_three_helper_items');
            $table->string('form_label', 120)->nullable()->after('guidance_three_link_text');
            $table->text('form_description')->nullable()->after('form_label');
            $table->string('website_label', 120)->nullable()->after('form_description');
            $table->string('subject_label', 120)->nullable()->after('website_label');
            $table->string('message_label', 120)->nullable()->after('subject_label');
            $table->string('submit_button_text', 120)->nullable()->after('message_label');
            $table->string('office_label', 120)->nullable()->after('submit_button_text');
            $table->string('business_hours_heading', 180)->nullable()->after('office_label');
            $table->string('closed_label', 80)->nullable()->after('business_hours_heading');
            $table->string('map_link_text', 120)->nullable()->after('closed_label');
        });

        Schema::table('cms_business_hours', function (Blueprint $table) {
            $table->string('holiday_text', 180)->nullable()->after('closes_at');
        });

        DB::table('cms_contact_content')->update([
            'hero_label' => 'Start a conversation',
            'response_primary_cta_text' => 'Request demo',
            'response_secondary_cta_text' => 'Request quote',
            'email_card_label' => 'Email',
            'phone_card_label' => 'Phone',
            'whatsapp_card_label' => 'WhatsApp',
            'inquiry_card_label' => 'Business inquiry',
            'guidance_label' => 'Prepare the conversation',
            'guidance_title' => 'Choose the path that matches your decision.',
            'guidance_description' => 'You do not need a finished specification. Clear operating context is more useful than a long feature list.',
            'guidance_one_title' => 'Business inquiry',
            'guidance_one_description' => 'Use the contact form for workflow problems, integration questions, delivery partnerships, or an initial technical discussion.',
            'guidance_one_helper_title' => 'Helpful context',
            'guidance_one_helper_items' => "Business objective and affected teams\nCurrent process or system\nConstraints, integrations, and timing",
            'guidance_two_title' => 'Product demo',
            'guidance_two_description' => 'Select the product or system you are evaluating. The session is organised around your roles, controls, reports, and deployment questions.',
            'guidance_two_helper_title' => 'What happens next',
            'guidance_two_helper_items' => "We confirm the evaluation context\nThe demonstration follows relevant workflows\nOpen fit and implementation questions are recorded",
            'guidance_two_link_text' => 'Request a demo',
            'guidance_three_title' => 'Quote request',
            'guidance_three_description' => 'Use the quote workflow when the scope is sufficiently clear to discuss commercial options, delivery assumptions, and dependencies.',
            'guidance_three_helper_title' => 'What improves accuracy',
            'guidance_three_helper_items' => "Required outcome and scope boundary\nUsers, data, and integrations\nTarget timing and decision process",
            'guidance_three_link_text' => 'Prepare a quote request',
            'form_label' => 'Tell us what you need',
            'form_description' => 'Include the current process, affected users, known integrations, timing constraints, and the result the business needs to measure. This helps us respond with a useful next step.',
            'website_label' => 'Website',
            'subject_label' => 'Subject',
            'message_label' => 'Message',
            'submit_button_text' => 'Send message',
            'office_label' => 'Office information',
            'business_hours_heading' => 'Business hours',
            'closed_label' => 'Closed',
            'map_link_text' => 'View location',
        ]);
    }

    public function down(): void
    {
        Schema::table('cms_business_hours', function (Blueprint $table) {
            $table->dropColumn('holiday_text');
        });

        Schema::table('cms_contact_content', function (Blueprint $table) {
            $table->dropColumn([
                'hero_label', 'response_primary_cta_text', 'response_secondary_cta_text', 'email_card_label', 'phone_card_label', 'whatsapp_card_label', 'inquiry_card_label', 'guidance_label', 'guidance_title', 'guidance_description', 'guidance_one_title', 'guidance_one_description', 'guidance_one_helper_title', 'guidance_one_helper_items', 'guidance_two_title', 'guidance_two_description', 'guidance_two_helper_title', 'guidance_two_helper_items', 'guidance_two_link_text', 'guidance_three_title', 'guidance_three_description', 'guidance_three_helper_title', 'guidance_three_helper_items', 'guidance_three_link_text', 'form_label', 'form_description', 'website_label', 'subject_label', 'message_label', 'submit_button_text', 'office_label', 'business_hours_heading', 'closed_label', 'map_link_text',
            ]);
        });
    }
};
