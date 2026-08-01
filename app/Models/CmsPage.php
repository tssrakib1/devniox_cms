<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = ['key', 'status', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'open_graph_image_path', 'is_indexable', 'updated_by'];

    protected function casts(): array
    {
        return ['is_indexable' => 'boolean'];
    }

    public function home()
    {
        return $this->hasOne(CmsHomeContent::class);
    }

    public function about()
    {
        return $this->hasOne(CmsAboutContent::class);
    }

    public function contact()
    {
        return $this->hasOne(CmsContactContent::class);
    }

    public function simpleContent()
    {
        return $this->hasOne(CmsSimplePageContent::class);
    }

    public function whyItems()
    {
        return $this->hasMany(HomeWhyItem::class)->orderBy('sort_order');
    }

    public function statistics()
    {
        return $this->hasMany(HomeStatistic::class)->orderBy('sort_order');
    }

    public function trustItems()
    {
        return $this->hasMany(HomeTrustItem::class)->orderBy('sort_order');
    }

    public function industryItems()
    {
        return $this->hasMany(HomeIndustryItem::class)->orderBy('sort_order');
    }

    public function processItems()
    {
        return $this->hasMany(HomeProcessItem::class)->orderBy('sort_order');
    }

    public function technologyItems()
    {
        return $this->hasMany(HomeTechnologyItem::class)->orderBy('sort_order');
    }

    public function faqItems()
    {
        return $this->hasMany(HomeFaqItem::class)->orderBy('sort_order');
    }

    public function coreValues()
    {
        return $this->hasMany(AboutCoreValue::class)->orderBy('sort_order');
    }

    public function workItems()
    {
        return $this->hasMany(AboutWorkItem::class)->orderBy('sort_order');
    }

    public function businessHours()
    {
        return $this->hasMany(CmsBusinessHour::class)->orderBy('day_of_week');
    }
}
