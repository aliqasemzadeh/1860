<?php

use App\Models\Content\Box;
use App\Models\Content\Post;
use App\Models\Shop\Product;
use Livewire\Livewire;

test('post index page renders successfully', function () {
    Post::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'summary' => 'This is a test article summary.',
        'content' => '<p>Content of article</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Livewire::test(\App\Livewire\Main\Content\Post\Index::class)
        ->assertStatus(200)
        ->assertSee('Test Article')
        ->assertSee('This is a test article summary.');
});

test('post view page renders successfully', function () {
    $post = Post::create([
        'title' => 'Detailed Article',
        'slug' => 'detailed-article',
        'summary' => 'Article summary goes here',
        'content' => '<p>Article detailed body content</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    Livewire::test(\App\Livewire\Main\Content\Post\View::class, ['slug' => $post->slug])
        ->assertStatus(200)
        ->assertSee('Detailed Article')
        ->assertSee('Article summary goes here')
        ->assertSee('Article detailed body content');
});

test('box index component renders successfully', function () {
    Box::create([
        'title_fa' => 'باکس تستی',
        'title_en' => 'test-box',
        'status' => true,
        'color_theme' => [
            'bg' => '#18181b',
            'text' => '#ffffff',
            'accent' => '#14b8a6',
        ],
    ]);

    Livewire::test(\App\Livewire\Main\Content\Box\Index::class)
        ->assertStatus(200)
        ->assertSee('باکس تستی');
});

test('box view page renders successfully', function () {
    $box = Box::create([
        'title_fa' => 'باکس تستی ویژه',
        'title_en' => 'special-test-box',
        'status' => true,
        'color_theme' => [
            'bg' => '#09090b',
            'text' => '#fafafa',
            'accent' => '#06b6d4',
        ],
    ]);

    Livewire::test(\App\Livewire\Main\Content\Box\View::class, ['id' => $box->id, 'slug' => $box->title_en])
        ->assertStatus(200)
        ->assertSee('باکس تستی ویژه');
});

test('banner panel index, create, reorder, soft delete, restore and homepage display work', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    foreach (['content_banner_index', 'content_banner_create', 'content_banner_edit', 'content_banner_delete'] as $perm) {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm]);
    }

    $user = \App\Models\User::create(['mobile' => '0912'.random_int(1000000, 9999999)]);
    $user->givePermissionTo(['content_banner_index', 'content_banner_create', 'content_banner_edit', 'content_banner_delete']);

    $desktopUpload = \Illuminate\Http\UploadedFile::fake()->image('hero-desktop.jpg', 1800, 700);
    $mobileUpload = \Illuminate\Http\UploadedFile::fake()->image('hero-mobile.jpg', 800, 900);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Panel\Content\Banner\Create::class)
        ->set('form.title', 'بنر جشنواره پاییزه')
        ->set('form.link', '/search')
        ->set('form.is_active', true)
        ->set('desktop_file', $desktopUpload)
        ->set('mobile_file', $mobileUpload)
        ->call('create')
        ->assertHasNoErrors();

    $banner1 = \App\Models\Content\Banner::query()->first();

    expect($banner1)->not->toBeNull()
        ->and($banner1->title)->toBe('بنر جشنواره پاییزه')
        ->and($banner1->link)->toBe('/search')
        ->and($banner1->desktop_image)->toEndWith('-desktop.webp')
        ->and($banner1->tablet_image)->toEndWith('-tablet.webp')
        ->and($banner1->mobile_image)->toEndWith('-mobile.webp');

    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($banner1->desktop_image);
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($banner1->tablet_image);
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($banner1->mobile_image);

    $banner2 = \App\Models\Content\Banner::create([
        'title' => 'بنر دوم',
        'link' => '/blog',
        'desktop_image' => 'banners/second-desktop.webp',
        'tablet_image' => 'banners/second-tablet.webp',
        'mobile_image' => 'banners/second-mobile.webp',
        'is_active' => true,
    ]);

    expect($banner1->fresh()->sort_order)->toBeLessThan($banner2->fresh()->sort_order);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Panel\Content\Banner\Index::class)
        ->assertStatus(200)
        ->assertSee('بنر جشنواره پاییزه')
        ->assertSee('بنر دوم')
        ->call('moveUp', $banner2->id);

    expect($banner2->fresh()->sort_order)->toBeLessThan($banner1->fresh()->sort_order);

    // Homepage renders active banners in order
    Livewire::test(\App\Livewire\Main\Dashboard\Index::class)
        ->assertStatus(200)
        ->assertSee('بنر جشنواره پاییزه')
        ->assertSee('بنر دوم');

    // Soft delete & restore
    Livewire::actingAs($user)
        ->test(\App\Livewire\Panel\Content\Banner\Index::class)
        ->call('delete', $banner1->id);

    expect($banner1->fresh()->trashed())->toBeTrue();

    Livewire::actingAs($user)
        ->test(\App\Livewire\Panel\Content\Banner\Index::class)
        ->set('trashedFilter', 'only')
        ->call('restore', $banner1->id);

    expect($banner1->fresh()->trashed())->toBeFalse();
});
