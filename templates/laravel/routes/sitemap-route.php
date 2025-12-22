<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sitemap Route
|--------------------------------------------------------------------------
|
| This route generates a dynamic XML sitemap. Customize the URLs array
| to include your application's pages.
|
*/

Route::get('/sitemap.xml', function () {
    $urls = [
        [
            'loc' => config('app.url'),
            'lastmod' => now()->toW3cString(),
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ],
        // Add more URLs here as your application grows
        // [
        //     'loc' => config('app.url') . '/about',
        //     'lastmod' => now()->toW3cString(),
        //     'changefreq' => 'monthly',
        //     'priority' => '0.8',
        // ],
    ];

    $content = view('sitemap', compact('urls'))->render();

    return response($content, 200)
        ->header('Content-Type', 'application/xml');
});
