<?php

use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['general', 'site_name', 'DevNiox', 'string', true],
            ['general', 'tagline', 'Digital products engineered for growth.', 'string', true],
            ['general', 'default_language', 'en', 'string', true],
            ['general', 'timezone', 'Asia/Dhaka', 'string', true],            ['branding', 'logo', '', 'image', true],
            ['branding', 'dark_logo', '', 'image', true],
            ['branding', 'favicon', '', 'image', true],            ['branding', 'theme_color', '#0d6efd', 'string', true],
            ['branding', 'admin_logo', '', 'image', true],
            ['branding', 'login_logo', '', 'image', true],            ['contact', 'company_name', 'DevNiox', 'string', true],
            ['contact', 'address', '', 'text', true],
            ['contact', 'phone', '', 'string', true],
            ['contact', 'mobile', '', 'string', true],
            ['contact', 'email', 'hello@devniox.test', 'email', true],
            ['contact', 'support_email', '', 'email', true],
            ['contact', 'sales_email', '', 'email', true],
            ['contact', 'google_maps_embed', '', 'text', true],            ['seo', 'meta_title', 'DevNiox - Business Software & Digital Products', 'string', true],
            ['seo', 'meta_description', 'DevNiox builds reliable business software and digital products.', 'text', true],
            ['seo', 'meta_keywords', '', 'string', true],
            ['seo', 'open_graph_image', '', 'image', true],
            ['seo', 'canonical_base_url', '', 'url', true],
            ['seo', 'robots_meta', 'index,follow', 'string', true],            ['seo', 'organization', 'DevNiox', 'string', true],            ['analytics', 'ga4_measurement_id', '', 'string', false],
            ['analytics', 'gtm_id', '', 'string', false],
            ['analytics', 'clarity_id', '', 'string', false],
            ['analytics', 'facebook_pixel_id', '', 'string', false],            ['email', 'smtp_host', '', 'string', false],
            ['email', 'smtp_port', '587', 'integer', false],
            ['email', 'smtp_username', '', 'string', false],
            ['email', 'smtp_password', '', 'secret', false],
            ['email', 'smtp_encryption', 'tls', 'string', false],
            ['email', 'from_name', 'DevNiox', 'string', false],
            ['email', 'from_email', 'hello@devniox.test', 'email', false],
            ['integrations', 'webhook_url', '', 'url', false],
            ['maintenance', 'enabled', '0', 'boolean', true],
            ['maintenance', 'message', 'We are completing scheduled maintenance. Please check back shortly.', 'text', true],
            ['maintenance', 'estimated_return', '', 'string', true],
            ['maintenance', 'allow_admin', '1', 'boolean', false],        ];

        foreach ($settings as [$group, $key, $value, $type, $public]) {
            Setting::firstOrCreate(['group' => $group, 'key' => $key], ['value' => $value, 'type' => $type, 'is_public' => $public]);
        }

        foreach (['facebook', 'linkedin', 'youtube', 'instagram', 'x', 'github', 'whatsapp'] as $order => $platform) {
            SocialLink::firstOrCreate(['platform' => $platform], ['display_order' => $order, 'is_visible' => false]);
        }
    }

    public function down(): void
    {
        // Settings are intentionally retained so customer configuration is not lost on rollback.
    }
};



