<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportBaguioPlaces extends Command
{
    protected $signature = 'places:import-baguio';

    protected $description = 'Import named places (streets, barangays, shops, amenities) for Baguio City from OpenStreetMap into the local places table';

    private const TAG_KEYS = ['place', 'amenity', 'shop', 'tourism', 'highway'];

    public function handle(): int
    {
        $query = '[out:json][timeout:120];'
            .'area["name"="Baguio"]["boundary"="administrative"]->.a;'
            .'('
            .'node["name"]["place"](area.a);'
            .'node["name"]["amenity"](area.a);'
            .'node["name"]["shop"](area.a);'
            .'node["name"]["tourism"](area.a);'
            .'way["name"]["highway"](area.a);'
            .');'
            .'out center tags;';

        $this->info('Querying Overpass API for Baguio places…');

        $response = Http::asForm()
            ->withHeaders(['User-Agent' => 'BaguioDeliveryImporter/1.0 ('.config('mail.from.address').')'])
            ->timeout(120)
            ->post('https://overpass-api.de/api/interpreter', ['data' => $query]);

        if ($response->failed()) {
            $this->error('Overpass request failed: '.$response->status());
            $this->line($response->body());

            return self::FAILURE;
        }

        $elements = $response->json('elements') ?? [];
        $this->info('Received '.count($elements).' elements. Importing…');

        $imported = 0;

        foreach ($elements as $element) {
            $tags = $element['tags'] ?? [];
            $name = $tags['name'] ?? null;

            $lat = $element['lat'] ?? $element['center']['lat'] ?? null;
            $lng = $element['lon'] ?? $element['center']['lon'] ?? null;

            if (! $name || $lat === null || $lng === null) {
                continue;
            }

            $category = null;
            foreach (self::TAG_KEYS as $key) {
                if (! empty($tags[$key])) {
                    $category = $tags[$key];
                    break;
                }
            }

            Place::updateOrCreate(
                ['osm_type' => $element['type'], 'osm_id' => $element['id']],
                ['name' => $name, 'category' => $category, 'lat' => $lat, 'lng' => $lng]
            );

            $imported++;
        }

        $this->info("Imported/updated {$imported} places.");

        return self::SUCCESS;
    }
}
