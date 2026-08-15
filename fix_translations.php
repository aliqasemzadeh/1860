<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.*\.blade\.php$/', RecursiveRegexIterator::GET_MATCH);

foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $original = $content;

    // Replace __('app. to __('general.
    $content = str_replace("__('app.", "__('general.", $content);
    // Replace @lang('app. to @lang('general.
    $content = str_replace("@lang('app.", "@lang('general.", $content);

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated: $path\n";
    }
}

$dir = new RecursiveDirectoryIterator(__DIR__ . '/app');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.*\.php$/', RecursiveRegexIterator::GET_MATCH);

foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $original = $content;

    $content = str_replace("__('app.", "__('general.", $content);

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated PHP: $path\n";
    }
}
