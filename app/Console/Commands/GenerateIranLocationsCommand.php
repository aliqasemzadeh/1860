<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateIranLocationsCommand extends Command
{
    protected $signature = 'iran:generate-locations';

    protected $description = 'Generate provinces.php and cities.php from GitHub JSON sources';

    public function handle(): int
    {
        $this->info('Downloading provinces.json...');
        $provincesResponse = Http::get('https://raw.githubusercontent.com/sajaddp/list-of-cities-in-Iran/refs/heads/main/dist/json/provinces.json');

        if ($provincesResponse->failed()) {
            $this->error('Failed to download provinces.json');

            return 1;
        }

        $provinces = $provincesResponse->json();
        $this->info('Downloaded '.count($provinces).' provinces');

        $this->info('Downloading cities.json...');
        $citiesResponse = Http::get('https://raw.githubusercontent.com/sajaddp/list-of-cities-in-Iran/refs/heads/main/dist/json/cities.json');

        if ($citiesResponse->failed()) {
            $this->error('Failed to download cities.json');

            return 1;
        }

        $cities = $citiesResponse->json();
        $this->info('Downloaded '.count($cities).' cities');

        // Generate provinces.php
        $this->info('Generating provinces.php...');
        $provincesContent = "<?php\n\nreturn [\n";
        foreach ($provinces as $province) {
            $id = $province['id'];
            $name = $province['name'];
            $provincesContent .= "    {$id} => '{$name}',\n";
        }
        $provincesContent .= "];\n";

        $provincesPath = lang_path('fa/provinces.php');
        file_put_contents($provincesPath, $provincesContent);
        $this->info("Generated: {$provincesPath}");

        // Group cities by province_id
        $this->info('Grouping cities by province...');
        $citiesByProvince = [];
        foreach ($cities as $city) {
            if (! isset($city['province_id'], $city['name'])) {
                continue;
            }
            $provinceId = (int) $city['province_id'];
            $cityName = $city['name'];

            if (! isset($citiesByProvince[$provinceId])) {
                $citiesByProvince[$provinceId] = [];
            }

            $citiesByProvince[$provinceId][] = $cityName;
        }

        // Sort cities within each province by name
        foreach ($citiesByProvince as $provinceId => $cityList) {
            sort($cityList, SORT_NATURAL);
            $citiesByProvince[$provinceId] = $cityList;
        }

        // Generate cities.php
        $this->info('Generating cities.php...');
        $citiesContent = "<?php\n\nreturn [\n";

        // Sort by province_id
        ksort($citiesByProvince);

        foreach ($citiesByProvince as $provinceId => $cityList) {
            $citiesContent .= "    {$provinceId} => [\n";
            $cityCounter = 1;
            foreach ($cityList as $cityName) {
                // Generate sequential ID: ProvinceID + 3-digit counter (e.g., 100001)
                $cityId = $provinceId.str_pad($cityCounter, 3, '0', STR_PAD_LEFT);
                // Escape single quotes in city names
                $escapedName = str_replace("'", "\\'", $cityName);
                $citiesContent .= "        '{$cityId}' => '{$escapedName}',\n";
                $cityCounter++;
            }
            $citiesContent .= "    ],\n";
        }

        $citiesContent .= "];\n";

        $citiesPath = lang_path('fa/cities.php');
        file_put_contents($citiesPath, $citiesContent);
        $this->info("Generated: {$citiesPath}");

        $totalCities = array_sum(array_map('count', $citiesByProvince));
        $this->info("Total cities grouped: {$totalCities} across ".count($citiesByProvince).' provinces');

        $this->info('Done!');

        return 0;
    }
}
