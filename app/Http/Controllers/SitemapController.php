<?php

namespace App\Http\Controllers;

use App\Services\Shop\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private readonly SitemapService $sitemapService) {}

    public function index(): Response
    {
        $urls = $this->sitemapService->urls();

        return response()
            ->view('sitemap.index', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        return response()
            ->view('sitemap.robots')
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
