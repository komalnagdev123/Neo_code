@extends('layouts.app')

@section('content')
<div class="container">

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Select Date Range to get Neo Data in the form of Bar Chart</div>
                <div class="card-body">
                    @if (session('error_message'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error_message') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('fetch-neo-stats') }}">
                        @csrf
                        <div class="form-group">
                            <label for="date" class="col-sm-4 col-form-label">Select Date</label>
                            <input class="form-control" name="filter_date" id="neo_date" value="" onchange="dateValidator();" />
{{--                            <input type="hidden" name="number_days" id="number_days" value="" />--}}
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    //Date Validator function

    function dateValidator()
    {
        var dateRange = $("#neo_date").val();

        var dateRangeArray = dateRange.split("-");

        var fromdate = dateRangeArray[0];
        var todate = dateRangeArray[1];

        var dt1 = new Date(fromdate);
        var dt2 = new Date(todate);

        var time_difference = dt2.getTime() - dt1.getTime();
        var result = time_difference / (1000 * 60 * 60 * 24);

        // $('#number_days').val(result);

    }
    //Date range picker
    // var start = moment().subtract(7, 'days');
    // var end = moment();

    function cb(start, end) {
        $('#neo_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }

    $('#neo_date').daterangepicker({
        // startDate: moment(start),
        // endDate: moment(end),
        showDropdowns: true,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            // 'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            // 'This Month': [moment().startOf('month'), moment().endOf('month')],
            // 'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
            //     'month')]
        },
        dateLimit: { days: 30 },
        locale: {
            format: 'Y/MM/DD'
        }
    }, cb);

</script>

@endsection
