<?php

namespace Database\Seeders;

use App\Models\CmsFooterContent;
use App\Models\CmsNavigationItem;
use App\Models\CmsPage;
use App\Models\Setting;
use App\Models\SocialLink;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);
        $this->seedFoundation();
    }

    public function seedFoundation(): void
    {
        $settings = [['company', 'name', 'DevNiox', 'string', true], ['company', 'tagline', 'Digital products engineered for growth.', 'string', true], ['contact', 'email', 'hello@devniox.test', 'email', true], ['contact', 'phone', '', 'string', true], ['contact', 'address', '', 'text', true], ['hours', 'weekdays', 'Monday–Friday, 9:00–18:00', 'string', true], ['social', 'linkedin', '', 'url', true], ['social', 'github', '', 'url', true], ['social', 'x', '', 'url', true], ['seo', 'meta_title', 'DevNiox — Business Software & Digital Products', 'string', true], ['seo', 'meta_description', 'DevNiox builds reliable business software and digital products.', 'text', true], ['seo', 'og_title', 'DevNiox', 'string', true], ['seo', 'og_description', 'Digital products engineered for growth.', 'text', true], ['branding', 'logo', '', 'image', true], ['branding', 'dark_logo', '', 'image', true], ['branding', 'favicon', '', 'image', true], ['branding', 'default_share_image', '', 'image', true], ['mail', 'host', '', 'string', false], ['mail', 'port', '587', 'integer', false], ['mail', 'username', '', 'string', false], ['mail', 'password', '', 'secret', false], ['mail', 'encryption', 'tls', 'string', false], ['analytics', 'google_tag_id', '', 'string', false]];
        foreach ($settings as [$group, $key, $value, $type, $is_public]) {
            Setting::updateOrCreate(compact('group', 'key'), compact('value', 'type', 'is_public'));
        }
        $globalSettings = [['general', 'short_description', 'Digital products engineered for growth.', 'text', true], ['general', 'website_name', 'DevNiox', 'string', true], ['general', 'default_language', 'en', 'string', true], ['general', 'timezone', 'Asia/Dhaka', 'string', true], ['general', 'date_format', 'M j, Y', 'string', true], ['general', 'time_format', 'g:i A', 'string', true], ['general', 'currency', 'USD', 'string', true], ['general', 'website_status', 'online', 'string', true], ['branding', 'apple_touch_icon', '', 'image', true], ['branding', 'theme_color', '#0d6efd', 'string', true], ['contact', 'support_email', '', 'email', true], ['contact', 'sales_email', '', 'email', true], ['contact', 'whatsapp', '', 'string', true], ['contact', 'maps_url', '', 'url', true], ['seo', 'meta_keywords', '', 'string', true], ['seo', 'canonical_strategy', 'current_url', 'string', true], ['seo', 'robots_default', 'index,follow', 'string', true], ['seo', 'organization_schema', 'DevNiox', 'string', true], ['analytics', 'ga_measurement_id', '', 'string', true], ['analytics', 'gtm_id', '', 'string', true], ['analytics', 'clarity_id', '', 'string', true], ['analytics', 'meta_pixel_id', '', 'string', true], ['analytics', 'head_scripts', '', 'text', true], ['analytics', 'footer_scripts', '', 'text', true], ['email', 'sender_name', 'DevNiox', 'string', false], ['email', 'sender_address', 'hello@devniox.test', 'email', false], ['email', 'reply_to', 'hello@devniox.test', 'email', false], ['email', 'lead_notification_email', 'hello@devniox.test', 'email', false], ['email', 'notifications_enabled', '1', 'boolean', false], ['integrations', 'whatsapp_enabled', '0', 'boolean', false], ['integrations', 'telegram_enabled', '0', 'boolean', false], ['integrations', 'live_chat_enabled', '0', 'boolean', false], ['integrations', 'external_api_enabled', '0', 'boolean', false], ['integrations', 'external_api_url', '', 'url', false], ['maintenance', 'enabled', '0', 'boolean', true], ['maintenance', 'message', 'We are completing scheduled maintenance. Please check back shortly.', 'text', true], ['maintenance', 'estimated_return', '', 'string', true], ['maintenance', 'allow_admin', '1', 'boolean', false]];
        foreach ($globalSettings as [$group,$key,$value,$type,$is_public]) {
            Setting::updateOrCreate(compact('group', 'key'), compact('value', 'type', 'is_public'));
        }
        foreach (['facebook', 'linkedin', 'github', 'youtube', 'instagram', 'x', 'tiktok', 'telegram'] as $order => $platform) {
            SocialLink::firstOrCreate(['platform' => $platform], ['display_order' => $order, 'is_visible' => false]);
        }
        $home = CmsPage::firstOrCreate(['key' => 'home'], ['status' => 'published', 'meta_title' => 'DevNiox — Business Software & Digital Products', 'meta_description' => 'DevNiox builds reliable business software and digital products.', 'is_indexable' => true]);
        $home->home()->firstOrCreate([], ['hero_heading' => 'We engineer digital advantage.', 'hero_subheading' => 'Software · Systems · Growth', 'hero_description' => 'DevNiox designs dependable software, connected business systems, and digital products built to scale with your business.', 'primary_button_text' => 'Start a conversation', 'primary_button_url' => route('contact', [], false), 'intro_title' => 'Engineering built around outcomes', 'intro_description' => 'We combine product thinking, sound architecture, and disciplined execution.', 'products_title' => 'Featured products', 'services_title' => 'Featured services', 'ai_title' => '', 'portfolio_title' => 'Featured projects', 'articles_title' => 'Latest articles', 'ecosystem_enabled' => true, 'ecosystem_label' => 'OUR ECOSYSTEM', 'ecosystem_title' => 'Powerful Platforms. One Parent Company.', 'ecosystem_description' => 'Ravoltify Technologies builds and manages a growing ecosystem of software products and digital platforms designed to help businesses operate more efficiently.', 'ecosystem_note' => 'All platforms are developed, maintained and supported by Ravoltify Technologies.']);
        $about = CmsPage::firstOrCreate(['key' => 'about'], ['status' => 'published', 'meta_title' => 'About DevNiox', 'meta_description' => 'Learn about DevNiox.', 'is_indexable' => true]);
        $about->about()->firstOrCreate([], ['hero_heading' => 'Engineering with intent.', 'hero_description' => 'We combine product thinking, strong architecture, and measured execution.', 'story_title' => 'Our story', 'story_description' => 'DevNiox helps organisations turn ambitious ideas into dependable digital systems.', 'mission_title' => 'Our mission', 'mission_description' => 'Create useful technology that produces lasting business value.', 'vision_title' => 'Our vision', 'vision_description' => 'A world where excellent digital systems are accessible to every ambitious organisation.']);
        $contact = CmsPage::firstOrCreate(['key' => 'contact'], ['status' => 'published', 'meta_title' => 'Contact DevNiox', 'meta_description' => 'Contact DevNiox about software products and engineering services.', 'is_indexable' => true]);
        $contact->contact()->firstOrCreate([], ['hero_heading' => 'Let’s build something useful.', 'hero_description' => 'Tell us what you are working on and our team will respond with a practical next step.', 'company_name' => 'DevNiox', 'email' => 'hello@devniox.test', 'success_message' => 'Thank you. Your message has been received.']);
        for ($day = 0; $day < 7; $day++) {
            $contact->businessHours()->firstOrCreate(['day_of_week' => $day], ['is_closed' => in_array($day, [0, 6]), 'opens_at' => '09:00', 'closes_at' => '18:00']);
        }
        foreach ([['Home', '/'], ['Products', '/products'], ['Services', '/services'], ['Portfolio', '/portfolio'], ['Blog', '/blog'], ['About', '/about'], ['Contact', '/contact']] as $i => [$label,$url]) {
            CmsNavigationItem::firstOrCreate(['location' => 'header', 'url' => $url], ['label' => $label, 'display_order' => $i, 'is_visible' => true]);
        }
        CmsFooterContent::firstOrCreate([], ['copyright' => '© '.date('Y').' DevNiox. All rights reserved.', 'short_description' => 'Digital products engineered for growth.', 'quick_links_heading' => 'Quick Links', 'products_heading' => 'Products', 'services_heading' => 'Services', 'ai_heading' => '', 'blog_heading' => 'Knowledge Center']);
        // DemoDataSeeder is intentionally opt-in. The installer may call it
        // only after the owner selects "Install Demo Data".
    }
}

