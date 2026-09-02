<?php

namespace App\Http\Controllers;

use App\Services\Shop\TorobProductFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TorobProductFeedController extends Controller
{
    public function __invoke(Request $request, TorobProductFeedService $feed): JsonResponse
    {
        $input = $request->json()->all();
        $modes = collect(['page_urls', 'page_uniques', 'page'])
            ->filter(fn (string $key): bool => array_key_exists($key, $input));

        if ($modes->count() !== 1) {
            return response()->json(['error' => 'Provide exactly one request mode.'], 400);
        }

        $rules = match ($modes->first()) {
            'page_urls' => [
                'page_urls' => ['required', 'array', 'min:1', 'max:100'],
                'page_urls.*' => ['required', 'url', 'max:1500'],
            ],
            'page_uniques' => [
                'page_uniques' => ['required', 'array', 'min:1', 'max:100'],
                'page_uniques.*' => ['required', 'string', 'max:200'],
            ],
            default => [
                'page' => ['required', 'integer', 'min:1'],
                'sort' => ['required', 'in:date_added_desc,date_updated_desc'],
            ],
        };

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        return response()->json($feed->products($validator->validated()));
    }
}
