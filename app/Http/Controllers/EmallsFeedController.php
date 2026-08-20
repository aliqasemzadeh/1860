<?php

namespace App\Http\Controllers;

use App\Services\Shop\EmallsFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmallsFeedController extends Controller
{
    public function __invoke(Request $request, EmallsFeedService $feed): JsonResponse
    {
        $payload = $feed->paginate(
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('item_per_page', config('emalls.default_per_page')),
        );

        return response()->json($payload, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/json; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=300');
    }
}
