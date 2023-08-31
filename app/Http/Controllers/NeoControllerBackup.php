<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Exception;
// use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
class NeoController extends Controller
{
    private $startDate;
    private $endDate;

    public function dateRange()
    {
        return view('date_range');
    }

    public function getApiData(Request $request)
    {
        //exploade date to get startDate and endDate
        $dates = explode(' - ', $request->filter_date);
        $startDate = date('Y-m-d', strtotime($dates[0]));
        $endDate = date('Y-m-d', strtotime($dates[1]));
        $apiKey = env('NEO_API_KEY');

        //finding difference between 2 dates
        $startDateParse = Carbon::parse($startDate);
        $endDateParse = Carbon::parse($endDate);
        $diff = $startDateParse->diffInDays($endDateParse);

        try
        {
             //validating difference between 2 dates. It should not be greater than 7 days as Neo API supports only 7 days diffrence.
            if($diff > 7)
            {
                return redirect()->back()
                ->with('error_message', 'Difference between 2 dates should not be greater than 7 days.');
            }

            //getting all data from Neo API
            $response = Http::get("https://api.nasa.gov/neo/rest/v1/feed", [
            'start_date' => $startDate,
            'end_date' => $endDate,
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

                $getAdditionalAsteroidData = $this->getAdditionalAsteroidData($neoApiData,$asteroidsCount);

                return view('barchart', compact('asteroidsCount','getAdditionalAsteroidData','neoDatesdata', 'neoAstroidData'));
            }
            else
            {
                return redirect()->back()
                ->with('error_message', 'Oops!Internal Server Error.Please Try Again Later.');
            }
        }
        catch (Exception $e)
        {
            return redirect()->back()
            ->with('error_message', 'Oops!Internal Server Error.Please Try Again Later.');
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

    private function getAdditionalAsteroidData($data,$asteroidsCount)
    {
        //creating array to return all calculated data
        $additionalData = [];

        // Variable inizialization to find the fastest asteroid from the data
        $maxSpeed = 0;

         // Variable inizialization to find the closest asteroid from the data
        $closestAsteroid = null;
        $closestDistance = PHP_INT_MAX;

        // Variable inizialization to calculate the average size of asteroids from the data
        $totalSize = 0;

        foreach ($data['near_earth_objects'] as $dateAsteroids) {

            foreach ($dateAsteroids as $asteroid) {

                //calculate Speed
                $speedKph = $asteroid['close_approach_data'][0]['relative_velocity']['kilometers_per_hour'];

                if ($speedKph > $maxSpeed) {
                    $maxSpeed = $speedKph;
                    $additionalData['fastest_asteroid_id'] = $asteroid['id'];
                    $additionalData['max_speed'] = $maxSpeed;
                }

                // Calculate Distance
                $distance = $asteroid['close_approach_data'][0]['miss_distance']['kilometers'];

                if ($distance < $closestDistance) {
                    $additionalData['closest_asteroid_id'] = $asteroid['id'];
                    $additionalData['closest_distance'] = $distance;
                }

                //calculate Average Size
                $totalSize += $asteroid['estimated_diameter']['kilometers']['estimated_diameter_max'];
                $averageSize = $asteroidsCount > 0 ? ($totalSize / $asteroidsCount) : 0;

                $additionalData['average_size'] = $averageSize;
            }
        }

        return $additionalData;
    }

    // $request->request->add([
        //     'start_date' => Carbon::parse($dates[0]),
        //     'end_date' => Carbon::parse($dates[1]),
        // ]);

        // $this->validate($request, [
        //     'filter_date' => ['required', new DateInterval()],
        // ]);

}
