<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['privacy_url', 'terms_url', 'cookies_url', 'about_url', 'contact_url', 'demo_url', 'quote_url'] as $column) {
            if (Schema::hasColumn('cms_footer_content', $column)) {
                DB::statement("ALTER TABLE cms_footer_content MODIFY `$column` TEXT NULL");
            }
        }

        $this->addString('company_heading', 'blog_heading', 100);
        $this->addString('resources_heading', 'company_heading', 100);
        $this->addString('about_label', 'cookies_url', 100);
        $this->addText('about_url', 'about_label');
        $this->addString('contact_label', 'about_url', 100);
        $this->addText('contact_url', 'contact_label');
        $this->addString('demo_label', 'contact_url', 100);
        $this->addText('demo_url', 'demo_label');
        $this->addString('quote_label', 'demo_url', 100);
        $this->addText('quote_url', 'quote_label');
        $this->addString('blog_label', 'quote_url', 100);
        $this->addText('blog_url', 'blog_label');
        $this->addString('rss_label', 'blog_url', 100);
        $this->addText('rss_url', 'rss_label');
        $this->addString('sitemap_label', 'rss_url', 100);
        $this->addText('sitemap_url', 'sitemap_label');
        $this->addString('privacy_label', 'sitemap_url', 100);
        $this->addString('terms_label', 'privacy_label', 100);
        $this->addString('cookies_label', 'terms_label', 100);
        $this->addString('contact_heading', 'cookies_label', 120);
        $this->addText('contact_text', 'contact_heading');
        $this->addString('address_label', 'contact_text', 100);
        $this->addString('email_label', 'address_label', 100);
        $this->addString('phone_label', 'email_label', 100);
        $this->addString('whatsapp_label', 'phone_label', 100);
        $this->addText('business_hours_text', 'whatsapp_label');
        $this->addText('support_hours_text', 'business_hours_text');
        $this->addString('cta_title', 'support_hours_text', 160);
        $this->addText('cta_description', 'cta_title');
        $this->addString('cta_button_text', 'cta_description', 120);
        $this->addText('cta_button_url', 'cta_button_text');
        $this->addString('newsletter_heading', 'cta_button_url', 160);
        $this->addText('newsletter_description', 'newsletter_heading');
        $this->addString('newsletter_placeholder', 'newsletter_description', 160);
        $this->addString('newsletter_button_text', 'newsletter_placeholder', 120);
        $this->addString('bottom_text', 'newsletter_button_text', 255);
        $this->addString('made_by_text', 'bottom_text', 160);
        $this->addString('powered_by_text', 'made_by_text', 160);
        $this->addString('version_text', 'powered_by_text', 80);

        DB::table('cms_footer_content')->update([
            'company_heading' => 'Company',
            'resources_heading' => 'Resources',
            'about_label' => 'About',
            'about_url' => '/about',
            'contact_label' => 'Contact',
            'contact_url' => '/contact',
            'demo_label' => 'Request demo',
            'demo_url' => '/request-demo',
            'quote_label' => 'Request quote',
            'quote_url' => '/request-quote',
            'blog_label' => 'Knowledge center',
            'blog_url' => '/blog',
            'rss_label' => 'RSS feed',
            'rss_url' => '/blog/feed.xml',
            'sitemap_label' => 'Sitemap',
            'sitemap_url' => '/sitemap.xml',
            'privacy_label' => 'Privacy Policy',
            'terms_label' => 'Terms & Conditions',
            'cookies_label' => 'Cookies',
            'contact_heading' => 'Start a conversation',
            'contact_text' => 'Tell us where your business needs better software.',
            'address_label' => 'Address',
            'email_label' => 'Email',
            'phone_label' => 'Phone',
            'whatsapp_label' => 'WhatsApp',
            'bottom_text' => 'Software products - Enterprise systems - Business automation',
        ]);
    }

    public function down(): void
    {
        $columns = ['company_heading', 'resources_heading', 'about_label', 'about_url', 'contact_label', 'contact_url', 'demo_label', 'demo_url', 'quote_label', 'quote_url', 'blog_label', 'blog_url', 'rss_label', 'rss_url', 'sitemap_label', 'sitemap_url', 'privacy_label', 'terms_label', 'cookies_label', 'contact_heading', 'contact_text', 'address_label', 'email_label', 'phone_label', 'whatsapp_label', 'business_hours_text', 'support_hours_text', 'cta_title', 'cta_description', 'cta_button_text', 'cta_button_url', 'newsletter_heading', 'newsletter_description', 'newsletter_placeholder', 'newsletter_button_text', 'bottom_text', 'made_by_text', 'powered_by_text', 'version_text'];
        $existing = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn('cms_footer_content', $column)));
        if ($existing !== []) {
            Schema::table('cms_footer_content', fn (Blueprint $table) => $table->dropColumn($existing));
        }
    }

    private function addString(string $column, string $after, int $length): void
    {
        if (! Schema::hasColumn('cms_footer_content', $column)) {
            Schema::table('cms_footer_content', fn (Blueprint $table) => $table->string($column, $length)->nullable()->after($after));
        }
    }

    private function addText(string $column, string $after): void
    {
        if (! Schema::hasColumn('cms_footer_content', $column)) {
            Schema::table('cms_footer_content', fn (Blueprint $table) => $table->text($column)->nullable()->after($after));
        }
    }
};
