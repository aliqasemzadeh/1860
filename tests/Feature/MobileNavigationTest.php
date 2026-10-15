<?php

use App\Models\Content\Box;
use App\Models\Shop\Category;
use Livewire\Livewire;

test('mobile bottom navigation renders on the home page', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee(__('general.home'));
    $response->assertSee(__('general.categories'));
    $response->assertSee(__('general.shopping_cart'));
});

test('sidebar categories render expandable groups without heavy command modals', function () {
    $parent = Category::create([
        'name' => 'لپ تاپ و تبلت',
        'slug' => 'laptops-tablets-test',
        'slug_fa' => 'لپ-تاپ-و-تبلت-تست',
        'main_category_id' => 0,
        'icon' => 'cube',
    ]);

    Category::create([
        'name' => 'لپ تاپ گیمینگ',
        'slug' => 'gaming-laptops-test',
        'slug_fa' => 'لپ-تاپ-گیمینگ-تست',
        'main_category_id' => $parent->id,
    ]);

    Livewire::test(\App\Livewire\Main\Sidebar\Categories::class)
        ->assertStatus(200)
        ->assertSee('لپ تاپ و تبلت')
        ->assertSee('لپ تاپ گیمینگ')
        ->assertDontSee('flux:command');
});

test('brand box index renders placeholder and supports lazy loading', function () {
    Box::create([
        'title_fa' => 'باکس پرسرعت',
        'title_en' => 'fast-box',
        'status' => true,
        'color_theme' => [
            'bg' => '#18181b',
            'text' => '#ffffff',
            'accent' => '#14b8a6',
        ],
    ]);

    $component = new \App\Livewire\Main\Content\Box\Index();
    $placeholder = $component->placeholder();

    expect($placeholder)->not->toBeNull();

    Livewire::test(\App\Livewire\Main\Content\Box\Index::class)
        ->assertStatus(200)
        ->assertSee('باکس پرسرعت');
});
