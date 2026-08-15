<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Artisan signatures for the admin Function panel / jobs
    |--------------------------------------------------------------------------
    */
    'allowed' => [
        'cache:clear',
        'optimize',
        'optimize:clear',
        'config:cache',
        'route:cache',
        'view:cache',
        'system:administrator:create-permissions-command',
        'system:administrator:create-roles-command',
        'app:add-water-mark-to-images-command',
        'app:optimize-images-command',
        'about',
        'schedule:list',
        'queue:failed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Explicitly blocked dangerous Artisan command prefixes / names
    |--------------------------------------------------------------------------
    */
    'blocked' => [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'db:wipe',
        'db:seed',
        'tinker',
        'env:encrypt',
        'env:decrypt',
        'key:generate',
        'down',
        'serve',
        'queue:flush',
        'queue:clear',
        'horizon:terminate',
        'vendor:publish',
        'package:discover',
        'make:',
        'schema:',
        'session:',
    ],

];
