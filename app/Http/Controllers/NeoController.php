<?php

namespace App\Http\Controllers;
use App\Rules\DateInterval;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
class NeoController extends Controller
{
    public function dateRange()
    {
        return view('date_range');
    }

    public function getApiData(Request $request)
    {
        //Explode date to get startDate and endDate
        $dates = explode(' - ', $request->filter_date);

        $request->request->add([
            'start_date' => Carbon::parse($dates[0]),
            'end_date' => Carbon::parse($dates[1]),
        ]);

        $apiKey = env('NEO_API_KEY');

        $this->validate($request, [
            'filter_date' => ['required', new DateInterval()],
        ]);

        try
        {
            //getting all data from Neo API
            $response = Http::get("https://api.nasa.gov/neo/rest/v1/feed", [
            'start_date' => $request->start_date->format('Y-m-d'),
            'end_date' => $request->end_date->format('Y-m-d'),
            'api_key' => $apiKey,
            ]);

            $neoApiData = $response->json();

            //if key exists in array means we got data successfully from API
            if (array_key_exists("element_count",$neoApiData) && array_key_exists("near_earth_objects",$neoApiData))
            {
                $asteroidsCount = $neoApiData['element_count'];
                $getNeoStatstics = $this->getNeoStatstics($neoApiData);

                $neoDatesdata = array_keys($getNeoStatstics);
                $neoAstroidData = array_values($getNeoStatstics);

                //for getting FastestAsteroidData
                $getFastestAsteroid = $this->getFastestAsteroid($neoApiData);

                //for getting ClosestAsteroidData
                $getClosestAsteroid = $this->getClosestAsteroid($neoApiData);

                //for getting Average Size
                $getAverageSizeOfAsteroid = $this->getAverageSizeOfAsteroid($neoApiData,$asteroidsCount);

                return view('barchart', compact('asteroidsCount','getFastestAsteroid','getClosestAsteroid','getAverageSizeOfAsteroid','neoDatesdata', 'neoAstroidData'));
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

    private function getNeoStatstics($neoApiData)
    {

        $neoDataByItems = [];
        foreach ($neoApiData['near_earth_objects'] as $key => $value) {
            $neoDataByItems[$key] = count($value);
        }

        //Sorting Data by date order
        ksort($neoDataByItems);

        return $neoDataByItems;
    }

    private function getFastestAsteroid($data)
    {
        //creating array to return all calculated data
        $fastestAsteroidData = [];

        // Variable inizialization to find the fastest asteroid from the data
        $maxSpeed = 0;

        foreach ($data['near_earth_objects'] as $dateAsteroids) {

            foreach ($dateAsteroids as $asteroid) {
                //calculate Speed

                $speedKph = $asteroid['close_approach_data'][0]['relative_velocity']['kilometers_per_hour'];

                if ($speedKph > $maxSpeed) {
                    $maxSpeed = $speedKph;
                    $fastestAsteroidData['fastest_asteroid_id'] = $asteroid['id'];
                    $fastestAsteroidData['max_speed'] = $maxSpeed;
                }
            }
        }

        return $fastestAsteroidData;
    }

    private function getClosestAsteroid($data)
    {
         //creating array to return all calculated data
         $closestAsteroidData = [];

         // Variable inizialization to find the closest asteroid from the data
         $closestAsteroid = null;
         $closestDistance = PHP_INT_MAX;

         foreach ($data['near_earth_objects'] as $dateAsteroids) {

            foreach ($dateAsteroids as $asteroid) {

                // Calculate Distance
                $distance = $asteroid['close_approach_data'][0]['miss_distance']['kilometers'];

                if ($distance < $closestDistance) {
                    $closestAsteroidData['closest_asteroid_id'] = $asteroid['id'];
                    $closestAsteroidData['closest_distance'] = $distance;
                }
            }
        }

        return $closestAsteroidData;
    }

    private function getAverageSizeOfAsteroid($data,$asteroidsCount)
    {

        // Variable inizialization to calculate the average size of asteroids from the data
        $totalSize = 0;

        foreach ($data['near_earth_objects'] as $dateAsteroids) {

            foreach ($dateAsteroids as $asteroid) {

                //calculate Average Size
                $totalSize += $asteroid['estimated_diameter']['kilometers']['estimated_diameter_max'];
                $averageSize = $asteroidsCount > 0 ? ($totalSize / $asteroidsCount) : 0;
            }
        }

        return $averageSize;
    }

}
