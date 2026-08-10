<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\MediaAsset;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCmsFunctionalTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::query()->where('role', 'admin')->firstOrFail();
        $this->actingAs($this->admin);
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    public function test_parent_company_settings_control_homepage_ecosystem(): void
    {
        Storage::fake('public');
        $payload = [
            'parent_company_name' => 'DEVNIOX QA PARENT', 'parent_company_badge' => 'QA Parent Company',
            'parent_company_description' => 'DEVNIOX QA DESCRIPTION', 'parent_highlight_1_title' => 'DEVNIOX QA HIGHLIGHT',
            'parent_highlight_1_description' => 'QA foundation description', 'parent_highlight_2_title' => 'QA ECOSYSTEM',
            'parent_highlight_2_description' => 'QA ecosystem description', 'parent_highlight_3_title' => 'QA VISION',
            'parent_highlight_3_description' => 'QA vision description', 'parent_company_logo' => UploadedFile::fake()->createWithContent('parent-logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"><rect width="20" height="20"/></svg>'),
        ];
        $this->put(route('admin.platforms.parent.update'), $payload)->assertRedirect();
        $this->get(route('admin.platforms.index'))->assertOk()->assertSee('DEVNIOX QA PARENT')->assertSee('DEVNIOX QA HIGHLIGHT');
        $this->assertDatabaseHas('settings', ['group' => 'company', 'key' => 'parent_company_name', 'value' => 'DEVNIOX QA PARENT']);
        $this->get(route('home'))->assertOk()->assertSee('DEVNIOX QA PARENT')->assertSee('DEVNIOX QA DESCRIPTION')->assertSee('DEVNIOX QA HIGHLIGHT')->assertSee('company/');
    }

    public function test_admin_authentication_and_protected_route(): void
    {
        auth()->logout();
        $this->assertGuest();
        $this->get(route('login'))->assertOk();
        $this->post(route('login'), [
            'email' => $this->admin->email,
            'password' => env('ADMIN_PASSWORD'),
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_product_validation_create_update_frontend_and_delete(): void
    {
        $category = DB::table('product_categories')->where('is_active', true)->firstOrFail();
        $payload = [
            'product_category_id' => $category->id,
            'name' => 'DEVNIOX_QA_PRODUCT',
            'slug' => 'devniox-qa-product',
            'version' => '1.0.0',
            'status' => 'published',
            'is_featured' => 0,
            'display_order' => 999999,
            'short_description' => 'A temporary QA product for functional verification.',
            'full_description' => 'DEVNIOX_QA_PRODUCT full description for frontend verification.',
            'seo' => ['is_indexable' => 1],
        ];

        $this->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [])
            ->assertRedirect();
        $this->assertDatabaseMissing('products', ['slug' => $payload['slug']]);

        $this->post(route('admin.products.store'), $payload)->assertRedirect();
        $product = Product::query()->where('slug', $payload['slug'])->firstOrFail();
        $this->assertSame('DEVNIOX_QA_PRODUCT', $product->name);
        $this->assertSame((int) $category->id, (int) $product->product_category_id);

        $payload['name'] = 'DEVNIOX_QA_PRODUCT_UPDATED';
        $payload['slug'] = 'devniox-qa-product-updated';
        $this->put(route('admin.products.update', $product), $payload)->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => $payload['name'], 'slug' => $payload['slug']]);

        $this->get(route('products.show', $payload['slug']))
            ->assertOk()
            ->assertSee('DEVNIOX_QA_PRODUCT_UPDATED');

        $this->delete(route('admin.products.destroy', $product))->assertRedirect(route('admin.products.index'));
        $this->assertNotNull(DB::table('products')->where('id', $product->id)->value('deleted_at'));
    }

    public function test_service_create_update_frontend_and_delete(): void
    {
        $category = DB::table('service_categories')->where('status', 'published')->whereNull('deleted_at')->firstOrFail();
        $payload = [
            'service_category_id' => $category->id,
            'name' => 'DEVNIOX_QA_SERVICE',
            'slug' => 'devniox-qa-service',
            'status' => 'published',
            'is_featured' => 0,
            'display_order' => 999999,
            'short_description' => 'A temporary QA service for functional verification.',
            'full_description' => 'DEVNIOX_QA_SERVICE full description for frontend verification.',
            'seo' => ['is_indexable' => 1],
        ];

        $this->post(route('admin.services.store'), $payload)->assertRedirect();
        $service = Service::query()->where('slug', $payload['slug'])->firstOrFail();
        $this->assertSame((int) $category->id, (int) $service->service_category_id);

        $payload['name'] = 'DEVNIOX_QA_SERVICE_UPDATED';
        $payload['slug'] = 'devniox-qa-service-updated';
        $this->put(route('admin.services.update', $service), $payload)->assertRedirect();
        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => $payload['name']]);
        $this->get(route('services.show', $payload['slug']))->assertOk()->assertSee('DEVNIOX_QA_SERVICE_UPDATED');

        $this->delete(route('admin.services.destroy', $service))->assertRedirect(route('admin.services.index'));
        $this->assertNotNull(DB::table('services')->where('id', $service->id)->value('deleted_at'));
    }

    public function test_admin_password_is_hashed_and_invalid_login_is_rejected(): void
    {
        $this->assertTrue(Hash::needsRehash($this->admin->password) === false || str_starts_with($this->admin->password, '$'));
        auth()->logout();
        $this->assertGuest();
        $this->post(route('login'), ['email' => $this->admin->email, 'password' => 'definitely-invalid'])->assertRedirect('/');
    }

    public function test_product_category_update_resolves_route_model_and_persists(): void
    {
        $payload = [
            'name' => 'DEVNIOX_QA_CATEGORY',
            'slug' => 'devniox-qa-category',
            'description' => 'Temporary category for QA.',
            'sort_order' => 999999,
            'is_active' => 1,
        ];

        $this->post(route('admin.product-categories.store'), $payload)->assertRedirect();
        $category = DB::table('product_categories')->where('slug', $payload['slug'])->firstOrFail();
        $payload['name'] = 'DEVNIOX_QA_CATEGORY_UPDATED';
        $payload['slug'] = 'devniox-qa-category-updated';

        $this->put(route('admin.product-categories.update', $category->id), $payload)->assertRedirect();
        $this->assertDatabaseHas('product_categories', ['id' => $category->id, 'name' => $payload['name'], 'slug' => $payload['slug']]);
        $this->delete(route('admin.product-categories.destroy', $category->id))->assertRedirect();
    }

    public function test_media_upload_validation_storage_database_and_delete(): void
    {
        Storage::fake('public');
        $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
        $file = UploadedFile::fake()->createWithContent('DEVNIOX_QA_MEDIA.png', base64_decode($png));

        $this->post(route('admin.media.store'), [
            'name' => 'DEVNIOX_QA_MEDIA',
            'alt_text' => 'QA image',
            'files' => [$file],
        ])->assertRedirect();

        $media = MediaAsset::query()->where('name', 'DEVNIOX_QA_MEDIA')->latest('id')->firstOrFail();
        $this->assertNotEmpty($media->file_path);
        Storage::disk($media->disk)->assertExists($media->file_path);
        $this->get(route('admin.media.index'))->assertOk()->assertSee('DEVNIOX_QA_MEDIA');

        $this->post(route('admin.media.store'), [
            'name' => 'DEVNIOX_QA_INVALID',
            'files' => [UploadedFile::fake()->create('unsafe.exe', 10, 'application/x-msdownload')],
        ])->assertSessionHasErrors('files.0');
        $this->assertDatabaseMissing('media_assets', ['name' => 'DEVNIOX_QA_INVALID']);

        $this->delete(route('admin.media.destroy', $media))->assertRedirect(route('admin.media.index'));
        $this->assertNotNull(DB::table('media_assets')->where('id', $media->id)->value('deleted_at'));
    }

    public function test_footer_cms_matches_public_footer_and_invalidates_cache(): void
    {
        $original = (array) DB::table('cms_footer_content')->first();
        $payload = [
            'copyright' => 'DEVNIOX_QA_COPYRIGHT',
            'short_description' => 'DEVNIOX_QA_FOOTER_DESCRIPTION',
            'company_heading' => 'DEVNIOX_QA_COMPANY',
            'resources_heading' => 'DEVNIOX_QA_RESOURCES',
            'conversation_heading' => 'DEVNIOX_QA_CONVERSATION',
            'conversation_description' => 'DEVNIOX_QA_CONVERSATION_DESCRIPTION',
            'contact_email' => 'qa-footer@devniox.test',
            'contact_phone' => '+8801700000000',
            'bottom_right_text' => 'DEVNIOX_QA_BOTTOM_TEXT',
        ];

        $this->get(route('admin.cms.footer.edit'))->assertOk();
        $this->put(route('admin.cms.footer.update'), $payload)->assertRedirect();
        $this->assertDatabaseHas('cms_footer_content', $payload);
        $this->get(route('admin.cms.footer.edit'))->assertOk()->assertSee('DEVNIOX_QA_COMPANY')->assertSee('+8801700000000');
        $this->get(route('home'))->assertOk()
            ->assertSee('DEVNIOX_QA_COMPANY')
            ->assertSee('DEVNIOX_QA_RESOURCES')
            ->assertSee('DEVNIOX_QA_CONVERSATION')
            ->assertSee('DEVNIOX_QA_FOOTER_DESCRIPTION')
            ->assertSee('qa-footer@devniox.test')
            ->assertSee('+8801700000000')
            ->assertSee('DEVNIOX_QA_COPYRIGHT')
            ->assertSee('DEVNIOX_QA_BOTTOM_TEXT');

        DB::table('cms_footer_content')->where('id', $original['id'])->update(collect($original)->except(['id'])->all());
    }
}
