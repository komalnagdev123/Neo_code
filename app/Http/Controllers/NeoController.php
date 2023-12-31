<?php

namespace App\Http\Controllers;
//use App\Rules\DateInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\NeoFormRequest;
use Exception;
use Illuminate\Http\Request;
class NeoController extends Controller
{
    public function dateRange()
    {
        return view('date_range');
    }

    public function getApiData(NeoFormRequest $request)
    {
        try
        {
            $neoApiData = $this->getNeoData($request->start_date, $request->end_date);

            if (isset($neoApiData['near_earth_objects'])) {

                $asteroidsCount = $neoApiData['element_count'];

                $asteroids = collect($neoApiData['near_earth_objects']);

                $chartData = $this->getChartData($asteroids);

                $getFastestAsteroidData = $this->getFastestAsteroidData($asteroids);

                $getClosestAsteroidData = $this->getClosestAsteroidData($asteroids);

                $getAverageSizeOfAsteroidData = $this->getAverageSizeOfAsteroidData($asteroids,$asteroidsCount);

                return view('barchart', compact('asteroidsCount','chartData','getFastestAsteroidData','getClosestAsteroidData','getAverageSizeOfAsteroidData'));

            }
            else
            {
                return redirect()->back()
                ->with('error_message', $neoApiData['error_message']);
            }
        }
        catch (Exception $e)
        {
            return redirect()->back()
            ->with('error_message', $e);
        }
    }

    private function getChartData(Collection $asteroids)
    {
        return $asteroids->mapWithKeys(fn ($dayAsteroids, $date) => [$date => count($dayAsteroids)])
                ->sortKeys()
                ->toArray();
    }

    private function getFastestAsteroidData(Collection $asteroids)
    {
        return $asteroids->flatMap(fn ($values) => $values)
                ->sortByDesc('close_approach_data.0.relative_velocity.kilometers_per_hour')
                ->first();
    }

    private function getClosestAsteroidData(Collection $asteroids)
    {
        return $asteroids->flatMap(fn ($values) => $values)
                ->sortBy('close_approach_data.0.miss_distance.kilometers')
                ->first();
    }

    private function getAverageSizeOfAsteroidData(Collection $asteroids,$asteroidsCount)
    {

        return $asteroids->flatMap(fn ($values) => $values)
                    ->average('estimated_diameter.kilometers.estimated_diameter_max');
    }

    private function getNeoData($startDate, $endDate): mixed
    {
        $apiKey = config('services.neo.key');

        $key = 'neo-' . $startDate . $endDate;

        return cache()->remember($key, now()->addDay(), fn () => Http::get("https://api.nasa.gov/neo/rest/v1/feed", [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'api_key' => $apiKey,
        ])->json());
    }
}
