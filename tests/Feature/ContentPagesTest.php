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
