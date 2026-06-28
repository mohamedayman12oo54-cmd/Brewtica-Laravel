<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Main_Category;
use App\Models\MenuItem;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    // ======= Helpers =======
    private function createAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function createCustomer(): User
    {
        return User::factory()->create();
    }

    // ==========================================
    // MAIN CATEGORY TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_view_all_main_categories(): void
    {
        $admin = $this->createAdmin();
        Main_Category::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'api')
                         ->getJson('/api/admin/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function test_admin_can_create_main_category(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'api')
                         ->postJson('/api/admin/categories', [
                             'name'        => 'Drinks',
                             'description' => 'All kinds of drinks',
                         ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('main_categories', ['name' => 'Drinks']);
    }

    /** @test */
    public function test_creating_main_category_requires_unique_name(): void
    {
        $admin = $this->createAdmin();
        Main_Category::factory()->create(['name' => 'Drinks']);

        $this->actingAs($admin, 'api')
             ->postJson('/api/admin/categories', ['name' => 'Drinks'])
             ->assertStatus(422);
    }

    /** @test */
    public function test_admin_can_update_main_category(): void
    {
        $admin    = $this->createAdmin();
        $category = Main_Category::factory()->create(['name' => 'Old Name']);

        $this->actingAs($admin, 'api')
             ->patchJson("/api/admin/categories/{$category->id}", ['name' => 'New Name'])
             ->assertStatus(200);

        $this->assertDatabaseHas('main_categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    /** @test */
    public function test_admin_can_delete_main_category_without_items(): void
    {
        $admin    = $this->createAdmin();
        $category = Main_Category::factory()->create();

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/categories/{$category->id}")
             ->assertStatus(200);

        $this->assertDatabaseMissing('main_categories', ['id' => $category->id]);
    }

    /** @test */
    public function test_admin_cannot_delete_main_category_with_menu_items(): void
    {
        $admin  = $this->createAdmin();
        $main   = Main_Category::factory()->create();
        $sub    = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);
        MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/categories/{$main->id}")
             ->assertStatus(422);

        $this->assertDatabaseHas('main_categories', ['id' => $main->id]);
    }

    /** @test */
    public function test_non_admin_cannot_access_category_routes(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer, 'api')
             ->getJson('/api/admin/categories')
             ->assertStatus(403);
    }

    /** @test */
    public function test_guest_cannot_access_category_routes(): void
    {
        $this->getJson('/api/admin/categories')
             ->assertStatus(401);
    }

    // ==========================================
    // SUB CATEGORY TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_create_sub_category_with_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $main  = Main_Category::factory()->create();

        $response = $this->actingAs($admin, 'api')
                         ->postJson('/api/admin/sub-categories', [
                             'main_category_id' => $main->id,
                             'name'              => 'Hot Drinks',
                             'image'             => UploadedFile::fake()->image('hot.jpg'),
                         ]);

        $response->assertStatus(201);

        $subCategory = SubCategory::where('name', 'Hot Drinks')->first();
        $this->assertNotNull($subCategory->image);
        Storage::disk('public')->assertExists($subCategory->image);
    }

    /** @test */
    public function test_creating_sub_category_rejects_non_image_file(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $main  = Main_Category::factory()->create();

        $this->actingAs($admin, 'api')
             ->postJson('/api/admin/sub-categories', [
                 'main_category_id' => $main->id,
                 'name'              => 'Hot Drinks',
                 'image'             => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
             ])
             ->assertStatus(422);
    }

    /** @test */
    public function test_admin_can_update_sub_category_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $main  = Main_Category::factory()->create();
        $sub   = SubCategory::factory()->create([
            'main_category_id' => $main->id,
            'image'            => 'sub-categories/old.jpg',
        ]);

        $response = $this->actingAs($admin, 'api')
                         ->patchJson("/api/admin/sub-categories/{$sub->id}", [
                             'image' => UploadedFile::fake()->image('new.jpg'),
                         ]);

        $response->assertStatus(200);

        $sub->refresh();
        $this->assertNotEquals('sub-categories/old.jpg', $sub->image);
    }

    /** @test */
    public function test_admin_cannot_delete_sub_category_with_menu_items(): void
    {
        $admin  = $this->createAdmin();
        $main   = Main_Category::factory()->create();
        $sub    = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);
        MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/sub-categories/{$sub->id}")
             ->assertStatus(422);
    }

    /** @test */
    public function test_admin_can_delete_sub_category_without_menu_items(): void
    {
        $admin = $this->createAdmin();
        $main  = Main_Category::factory()->create();
        $sub   = SubCategory::factory()->create(['main_category_id' => $main->id]);

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/sub-categories/{$sub->id}")
             ->assertStatus(200);

        $this->assertDatabaseMissing('sub_categories', ['id' => $sub->id]);
    }

    // ==========================================
    // SUB-SUB CATEGORY TESTS
    // ==========================================

    /** @test */
    public function test_admin_can_create_sub_sub_category_with_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $main  = Main_Category::factory()->create();
        $sub   = SubCategory::factory()->create(['main_category_id' => $main->id]);

        $response = $this->actingAs($admin, 'api')
                         ->postJson('/api/admin/sub-sub-categories', [
                             'sub_category_id' => $sub->id,
                             'name'             => 'Espresso',
                             'image'            => UploadedFile::fake()->image('espresso.jpg'),
                         ]);

        $response->assertStatus(201);

        $subSub = SubSubCategory::where('name', 'Espresso')->first();
        $this->assertNotNull($subSub->image);
    }

    /** @test */
    public function test_admin_cannot_delete_sub_sub_category_with_menu_items(): void
    {
        $admin  = $this->createAdmin();
        $main   = Main_Category::factory()->create();
        $sub    = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);
        MenuItem::factory()->create(['sub_sub_category_id' => $subSub->id]);

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/sub-sub-categories/{$subSub->id}")
             ->assertStatus(422);
    }

    /** @test */
    public function test_admin_can_delete_sub_sub_category_without_menu_items(): void
    {
        $admin  = $this->createAdmin();
        $main   = Main_Category::factory()->create();
        $sub    = SubCategory::factory()->create(['main_category_id' => $main->id]);
        $subSub = SubSubCategory::factory()->create(['sub_category_id' => $sub->id]);

        $this->actingAs($admin, 'api')
             ->deleteJson("/api/admin/sub-sub-categories/{$subSub->id}")
             ->assertStatus(200);

        $this->assertDatabaseMissing('sub_sub_categories', ['id' => $subSub->id]);
    }
}
