<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Exception;
use DateTime;

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
        $apiKey = "DEMO_KEY";

        $date1 = new DateTime($startDate);
        $date2 = new DateTime($endDate);
        $interval = $date1->diff($date2);
        
        //validating difference between 2 dates. It should not be greater than 7 days as Neo API supports only 7 days diffrence.
        if($interval->days > 7)
        {
            return redirect()->back()
            ->with('error_message', 'Difference between 2 dates should not be greater than 7 days.');
        }
        try
        {
            //getting all data from Neo API
            $response = Http::get("https://api.nasa.gov/neo/rest/v1/feed", [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'api_key' => $apiKey,
            ]);
    
            $neo_api_data = $response->json();
        
            //if key exists in array means we got data successfully from API
            if (array_key_exists("element_count",$neo_api_data) && array_key_exists("near_earth_objects",$neo_api_data))
            {
                $asteroidsCount = $neo_api_data['element_count'];
            
                $neo_data_by_items = [];
                foreach ($neo_api_data['near_earth_objects'] as $key => $value) {
                    $neo_data_by_items[$key] = count($value);
                }
                
                //Sorting Data by date order
                ksort($neo_data_by_items);
                
                $neo_dates_data = array_keys($neo_data_by_items);
                $neo_astroid_data = array_values($neo_data_by_items);

            
                $getAdditionalAsteroidData = $this->getAdditionalAsteroidData($neo_api_data,$asteroidsCount);
            
                return view('barchart', compact('asteroidsCount','getAdditionalAsteroidData','neo_dates_data', 'neo_astroid_data'));
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
                    $additionalData['fastestAsteroidId'] = $asteroid['id'];
                    $additionalData['maxSpeed'] = $maxSpeed;
                }

                // Calculate Distance
                $distance = $asteroid['close_approach_data'][0]['miss_distance']['kilometers'];
    
                if ($distance < $closestDistance) {
                    $additionalData['closestAsteroidId'] = $asteroid['id'];
                    $additionalData['closestDistance'] = $distance;
                }

                //calculate Average Size                
                $totalSize += $asteroid['estimated_diameter']['kilometers']['estimated_diameter_max'];
                $averageSize = $asteroidsCount > 0 ? ($totalSize / $asteroidsCount) : 0;

                $additionalData['averageSize'] = $averageSize;
            }
        }
    
        return $additionalData;
    }

}