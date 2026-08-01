<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_about_content', function (Blueprint $table) {
            $table->string('hero_label', 120)->nullable()->after('hero_banner_path');
            $table->string('hero_cta_text', 120)->nullable()->after('hero_label');
            $table->string('story_label', 120)->nullable()->after('story_image_path');
            $table->string('philosophy_label', 120)->nullable()->after('vision_description');
            $table->string('philosophy_title', 180)->nullable()->after('philosophy_label');
            $table->string('process_label', 120)->nullable()->after('philosophy_title');
            $table->string('process_title', 180)->nullable()->after('process_label');
        });

        Schema::create('cms_simple_page_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cms_page_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('hero_label', 120)->nullable();
            $table->string('hero_heading', 220);
            $table->text('hero_description')->nullable();
            $table->string('hero_banner_path')->nullable();
            $table->string('intro_label', 120)->nullable();
            $table->string('intro_title', 220)->nullable();
            $table->text('intro_description')->nullable();
            $table->text('body_content')->nullable();
            $table->json('steps')->nullable();
            $table->json('bullets')->nullable();
            $table->string('note_icon', 80)->nullable();
            $table->text('note_text')->nullable();
            $table->string('chapter_one_label', 120)->nullable();
            $table->string('chapter_two_label', 120)->nullable();
            $table->string('chapter_three_label', 120)->nullable();
            $table->string('name_label', 120)->nullable();
            $table->string('company_label', 120)->nullable();
            $table->string('email_label', 120)->nullable();
            $table->string('phone_label', 120)->nullable();
            $table->string('item_type_label', 120)->nullable();
            $table->string('item_id_label', 120)->nullable();
            $table->string('preferred_date_label', 120)->nullable();
            $table->string('preferred_time_label', 120)->nullable();
            $table->string('message_label', 120)->nullable();
            $table->string('budget_label', 120)->nullable();
            $table->string('budget_placeholder', 180)->nullable();
            $table->string('timeline_label', 120)->nullable();
            $table->string('timeline_placeholder', 180)->nullable();
            $table->string('requirement_details_label', 120)->nullable();
            $table->string('attachment_label', 120)->nullable();
            $table->string('attachment_helper', 180)->nullable();
            $table->string('optional_label', 80)->nullable();
            $table->string('submit_button_text', 120)->nullable();
            $table->string('success_message', 300)->nullable();
            $table->timestamps();
        });

        DB::table('cms_about_content')->update([
            'hero_label' => 'About DevNiox',
            'hero_cta_text' => 'Start a conversation',
            'story_label' => 'Company story',
            'philosophy_label' => 'Technology philosophy',
            'philosophy_title' => 'Principles behind the systems we build.',
            'process_label' => 'Why DevNiox',
            'process_title' => 'A development process grounded in trust.',
        ]);

        $pages = [
            'request-demo' => ['Request a Demo', 'Request a guided DevNiox product or service demonstration.', 'Guided product session', 'See the system through your workflow.', 'Choose the solution and a preferred time. The demonstration will focus on the operation you need to improve.'],
            'request-quote' => ['Request a Quote', 'Request a DevNiox project or solution quote.', 'Project planning', 'Turn the brief into an investment decision.', 'Share the outcome, constraints, and context. We will use them to prepare a relevant commercial response.'],
            'privacy-policy' => ['Privacy Policy', 'DevNiox privacy policy.', 'Privacy Policy', 'Privacy Policy', 'How DevNiox handles submitted information, communications, and website data.'],
            'terms-conditions' => ['Terms & Conditions', 'DevNiox terms and conditions.', 'Terms & Conditions', 'Terms & Conditions', 'Terms for using the DevNiox website and requesting services.'],
        ];

        foreach ($pages as $key => [$title, $description, $label, $heading, $heroDescription]) {
            $pageId = DB::table('cms_pages')->updateOrInsert(
                ['key' => $key],
                ['status' => 'published', 'meta_title' => $title, 'meta_description' => $description, 'is_indexable' => ! in_array($key, ['request-demo', 'request-quote'], true), 'created_at' => now(), 'updated_at' => now()]
            );
            $id = DB::table('cms_pages')->where('key', $key)->value('id');
            DB::table('cms_simple_page_content')->updateOrInsert(['cms_page_id' => $id], $this->defaults($key, $label, $heading, $heroDescription));
        }

        DB::table('cms_footer_content')->update(['privacy_url' => '/privacy-policy', 'terms_url' => '/terms-and-conditions']);
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_simple_page_content');
        Schema::table('cms_about_content', function (Blueprint $table) {
            $table->dropColumn(['hero_label', 'hero_cta_text', 'story_label', 'philosophy_label', 'philosophy_title', 'process_label', 'process_title']);
        });
    }

    private function defaults(string $key, string $label, string $heading, string $description): array
    {
        $common = ['hero_label' => $label, 'hero_heading' => $heading, 'hero_description' => $description, 'created_at' => now(), 'updated_at' => now()];

        return match ($key) {
            'request-demo' => $common + ['steps' => json_encode(['Select a system', 'Choose a time', 'Meet the product']), 'intro_label' => 'Request demo', 'intro_title' => 'A focused conversation, not a sales presentation.', 'note_icon' => 'camera-video', 'note_text' => 'Your request is routed to the team responsible for the selected system.', 'chapter_one_label' => 'Your details', 'chapter_two_label' => 'Solution', 'chapter_three_label' => 'Schedule', 'preferred_date_label' => 'Preferred date', 'preferred_time_label' => 'Preferred time', 'message_label' => 'What should we focus on?', 'submit_button_text' => 'Request demo', 'success_message' => 'Your demo request has been received.'],
            'request-quote' => $common + ['intro_label' => 'Request quote', 'intro_title' => 'Enough context creates a better proposal.', 'bullets' => json_encode(['Business objective', 'Preferred timeline', 'Investment context', 'Relevant documentation']), 'chapter_one_label' => 'Contact', 'chapter_two_label' => 'Project context', 'chapter_three_label' => 'Commercial context', 'budget_label' => 'Budget', 'budget_placeholder' => 'Example: USD 5,000-10,000', 'timeline_label' => 'Timeline', 'timeline_placeholder' => 'Example: Within 3 months', 'requirement_details_label' => 'Requirement details', 'attachment_label' => 'Supporting document', 'attachment_helper' => 'PDF, Office documents, or common images.', 'optional_label' => 'optional, up to 10 MB', 'submit_button_text' => 'Request quote', 'success_message' => 'Your quote request has been received.'],
            'privacy-policy' => $common + ['body_content' => "Information we collect\nWe collect information you submit through forms, including contact details, project context, and communication preferences.\n\nHow we use information\nWe use submitted information to respond to inquiries, prepare demos or quotes, deliver services, and improve website reliability.\n\nData retention\nWe keep business inquiry records only as long as needed for operational, legal, or service purposes.\n\nContact\nUse the contact page for privacy questions or data requests."],
            default => $common + ['body_content' => "Website use\nThe DevNiox website provides information about products, services, and business software capabilities.\n\nRequests and proposals\nSubmitting a demo or quote request does not create a binding agreement. Commercial terms are confirmed separately in writing.\n\nIntellectual property\nWebsite content, product names, and materials remain the property of DevNiox or their respective owners.\n\nLimitations\nWebsite information is provided for general business evaluation and may change without notice."],
        } + ['name_label' => 'Name', 'company_label' => 'Company', 'email_label' => 'Email', 'phone_label' => 'Phone', 'item_type_label' => 'Type', 'item_id_label' => 'Select item'];
    }
};
