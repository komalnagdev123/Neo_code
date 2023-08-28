@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Neo Bar Chart</div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                          <tr>
                            <td><span class="badge badge-info">Fastest Asteroid Id:-</span></td>
                            <td><span class="badge badge-light">{{$getAdditionalAsteroidData['fastest_asteroid_id']}}</span></td>
                            <td><span class="badge badge-info">Fastest Asteroid Speed(Km/h):-</span></td>
                            <td><span class="badge badge-light"> {{$getAdditionalAsteroidData['max_speed']}}</span></td>
                          </tr>
                          <tr>
                            <td><span class="badge badge-info">Closest Asteroid Id:-</span></td>
                            <td><span class="badge badge-light">{{$getAdditionalAsteroidData['closest_asteroid_id']}}</span></td>
                            <td><span class="badge badge-info">Closest Asteroid Distance(in KM):-</span></td>
                            <td><span class="badge badge-light"> {{$getAdditionalAsteroidData['closest_distance']}}</span></td>
                          </tr>
                          <tr>
                            <td><span class="badge badge-info">Average Size of Astroid:-</span></td>
                            <td><span class="badge badge-light">{{$getAdditionalAsteroidData['average_size']}}</span></td>
                            <td><span class="badge badge-info">Total No. of Asteroids:-</span></td>
                            <td><span class="badge badge-light"> {{$asteroidsCount}}</span></td>
                          </tr>
                        </tbody>
                      </table>
                    <br /><br />

                    <div class="map_canvas">
                        <canvas id="myChart" width="auto" height="100" style="border: solid;color: ivory;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Show Graph Data Liabraries-->
<script src="https://cdnjs.com/libraries/Chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js"></script>

<script type="text/javascript">
    var noOfAstroids = <?php  echo json_encode($neoAstroidData); ?>;
    var astroidsAppearDate = <?php  echo json_encode($neoDatesdata); ?>;

    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels:astroidsAppearDate, //Put data here to show on X Axis
            datasets: [
                {
                    label: "number of Asteroids",
                    data: noOfAstroids, // Add here data to show on Y axis
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 192, 203, 0.2)',
                        'rgba(169, 169, 169, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 192, 203, 1)',
                        'rgba(169, 169, 169, 1)'
                    ],
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    max: 100,
                    min: 0,
                    ticks: {
                        stepSize: 10
                    }
                }
            },
            plugins:
            {
                title: {
                    display: false,
                    text: 'Custom Chart Title'
                },
                legend: {
                    display: false,
                }
            }
        }
    });
</script>
