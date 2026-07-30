<?php

namespace App\Services;

use App\Models\CmsFooterContent;
use App\Models\CmsNavigationItem;
use App\Models\CmsPage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CmsService
{
    public function __construct(private ManagedImageService $images) {}

    public function page(string $key): CmsPage
    {
        return Cache::remember("cms.page.$key", 3600, function () use ($key) {
            $page = CmsPage::firstOrCreate(['key' => $key], ['status' => 'published', 'meta_title' => ucfirst($key).' — DevNiox', 'meta_description' => 'DevNiox digital solutions.', 'is_indexable' => true]);
            if ($key === 'home') {
                $page->home()->firstOrCreate([], ['hero_heading' => 'We engineer digital advantage.', 'hero_subheading' => 'Software · Systems · Growth', 'hero_description' => 'DevNiox designs dependable software, connected business systems, and digital products built to scale with your business.', 'primary_button_text' => 'Start a conversation', 'primary_button_url' => route('contact', [], false), 'intro_title' => 'Engineering built around outcomes', 'intro_description' => 'We combine product thinking, sound architecture, and disciplined execution.', 'products_title' => 'Featured products', 'services_title' => 'Featured services', 'ai_title' => '', 'portfolio_title' => 'Featured projects', 'articles_title' => 'Latest articles', 'ecosystem_enabled' => true, 'ecosystem_label' => 'OUR ECOSYSTEM', 'ecosystem_title' => 'Powerful Platforms. One Parent Company.', 'ecosystem_description' => 'Ravoltify Technologies builds and manages a growing ecosystem of software products and digital platforms designed to help businesses operate more efficiently.', 'ecosystem_note' => 'All platforms are developed, maintained and supported by Ravoltify Technologies.']);
            } elseif ($key === 'about') {
                $page->about()->firstOrCreate([], ['hero_heading' => 'Engineering with intent.', 'hero_description' => 'We combine product thinking, strong architecture, and measured execution.', 'story_title' => 'Our story', 'story_description' => 'DevNiox creates dependable digital systems.', 'mission_title' => 'Our mission', 'mission_description' => 'Create useful technology.', 'vision_title' => 'Our vision', 'vision_description' => 'Excellent digital systems for ambitious organisations.']);
            } else {
                $page->contact()->firstOrCreate([], ['hero_heading' => 'Let’s build something useful.', 'hero_description' => 'Tell us what you are working on.', 'company_name' => 'DevNiox', 'email' => 'hello@devniox.test', 'success_message' => 'Thank you. Your message has been received.']);
                for ($day = 0; $day < 7; $day++) {
                    $page->businessHours()->firstOrCreate(['day_of_week' => $day], ['is_closed' => in_array($day, [0, 6]), 'opens_at' => '09:00', 'closes_at' => '18:00']);
                }
            }

            return $page->load($key === 'home' ? ['home', 'whyItems', 'statistics'] : ($key === 'about' ? ['about', 'coreValues', 'workItems'] : ['contact', 'businessHours']));
        });
    }

    public function navigation(string $location)
    {
        return Cache::remember("cms.navigation.$location", 3600, fn () => CmsNavigationItem::visible()->where('location', $location)->whereNull('parent_id')->with(['children' => fn ($q) => $q->visible()])->orderBy('display_order')->get());
    }

    public function footer(): ?CmsFooterContent
    {
        return Cache::remember('cms.footer', 3600, fn () => CmsFooterContent::firstOrCreate([], ['copyright' => '© '.date('Y').' DevNiox. All rights reserved.', 'short_description' => 'Digital products engineered for growth.', 'quick_links_heading' => 'Quick Links', 'products_heading' => 'Products', 'services_heading' => 'Services', 'ai_heading' => '', 'blog_heading' => 'Knowledge Center']));
    }

    public function updatePage(CmsPage $page, array $d, int $user): void
    {
        DB::transaction(function () use ($page, $d, $user) {
            foreach (['open_graph_image', 'hero_background', 'intro_image', 'hero_banner', 'story_image'] as $key) {
                if (isset($d[$key])) {
                    $field = $key.'_path';
                    $relation = $page->key;
                    $model = $key === 'open_graph_image' ? $page : $page->{$relation};
                    $old = $model->{$field};
                    $model->{$field} = $this->images->store($d[$key], "cms/{$page->key}");
                    $model->save();
                    DB::afterCommit(fn () => $this->images->delete($old));
                }
            }$page->update(Arr::only($d, ['status', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'is_indexable']) + ['updated_by' => $user]);
            $relation = $page->key;
            $page->{$relation}()->update(Arr::except($d, ['status', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'is_indexable', 'open_graph_image', 'hero_background', 'intro_image', 'hero_banner', 'story_image', 'why_items', 'statistics', 'core_values', 'work_items', 'business_hours']));
            foreach (['why_items' => 'whyItems', 'statistics' => 'statistics', 'core_values' => 'coreValues', 'work_items' => 'workItems', 'business_hours' => 'businessHours'] as $key => $rel) {
                if (array_key_exists($key, $d)) {
                    $page->{$rel}()->delete();
                    $page->{$rel}()->createMany($d[$key]);
                }
            }
        });
        $this->forget($page->key);
    }

    public function updateNavigation(array $items): void
    {
        DB::transaction(function () use ($items) {
            CmsNavigationItem::query()->delete();
            foreach ($items as $item) {
                $children = $item['children'] ?? [];
                $parent = CmsNavigationItem::create(Arr::except($item, 'children'));
                foreach ($children as $child) {
                    $parent->children()->create($child);
                }
            }
        });
        Cache::forget('cms.navigation.header');
        Cache::forget('cms.navigation.footer');
    }

    public function updateFooter(array $d, int $user): void
    {
        CmsFooterContent::query()->first()->update($d + ['updated_by' => $user]);
        Cache::forget('cms.footer');
    }

    private function forget(string $key): void
    {
        Cache::forget("cms.page.$key");
    }
}

