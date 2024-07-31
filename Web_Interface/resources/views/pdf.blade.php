

<!DOCTYPE html>
<html>
<head>
    <title>Test Result Report</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">


    <style>
         /* General table styling */
    table {
        font-size: 12px; /* Adjust for overall font size */
        border-collapse: collapse;
    }

    .thead-text {
        font-family: 'Calibri', sans-serif;
        font-weight:50px;
        font-size: 10px;
        text-align: left;

    }

    .td-text {
        font-family: 'Courier New', Courier, monospace;
        font-size: 10px;
        text-align: left;
        color:blue;
        font-weight:bold;
    }

   




      
    </style>
</head>
<body class="p-8 relative">
   

    <div class="logo">   
    <img src="{{ public_path('head.png') }}" alt="Logo" alt="Logo" style="width:100%; height: auto; ">
      
    </div>
   <br>
   
    <h2 class="font-semibold mb-1 text-center" style="font-family: 'Courier Sans', monospace;color:blue;">  FINAL LABORATORY REPORT</h2>
  
    <div class="container-fluid mb-2">
    <hr class="my-1">
    <h4 class="font-semibold mb-1 text-md text-center">Patient Information</h4>
    <hr class="my-1">
    <div class="row">
        <div class="col-8">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col" class="thead-text">Patient ID</th>
                        <th scope="col" class="thead-text">SurName</th>
                        <th scope="col" class="thead-text">LastName</th>
                        <th scope="col" class="thead-text">Sex</th>
                        <th scope="col" class="thead-text">Age</th>
                        <th scope="col" class="thead-text">Contact</th>
                        <th scope="col" class="thead-text">Test Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="td-text">{{ $patient->id }}</td>
                        <td class="td-text">{{ $patient->sname }}</td>
                        <td class="td-text">{{ $patient->lname }}</td>
                        <td class="td-text">{{ $patient->sex }}</td>
                        <td class="td-text">{{ $patient->age }} {{ $patient->agecount }}</td>
                        <td class="td-text">{{ $patient->contact }}</td>
                        <td class="td-text">{{ $patient->created_at->format('m/d/Y') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



 <div class="container-fluid ">
 <hr class="my-1 border-top-1 border-bottom-1 border-primary">
    <h4 class="text-md font-semibold mb-1 text-center border-cyan">Test Results</h4>
    <hr class="my-1 border-top-1 border-bottom-1 border-primary">
    @if($testResults && !$testResults->isEmpty())
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
            <thead class="thead-dark" style="font-family: Calibri;">
    <tr>
        <th scope="col" class="thead-text">Test Carried Out</th>
        <th scope="col" class="thead-text">Test Result</th>
        <th scope="col" class="thead-text">Flag</th>
        <th scope="col" class="thead-text">Reference</th>
        <th scope="col" class="thead-text">  Units</th>
        <th scope="col" class="thead-text">Comment</th>
    </tr>
</thead>

<tbody>
    @foreach($testResults->whereNotNull('test_result') as $testResult)
        <tr>
            <td class=" td-text" style="font-family: 'Courier Sans', monospace;">{{ $testResult->test_carriedout }}</td>
            <td class=" td-text" style="font-family: 'Courier Sans', monospace;">{{ $testResult->test_result }}</td>
            <td class="td-text" style="font-family: 'Courier Sans', monospace;">{{ $testResult->flag }}</td>
            <td class=" td-text" style="font-family: 'Courier Sans', monospace;">{{ $testResult->range }}</td>
            <td class=" td-text" style="font-family: 'Courier Sans', monospace;">{{ $testResult->units }}</td>
            <td class="td-text" style="font-family: 'Courier Sans', monospace;">{{ $testResult->comment }}</td>
        </tr>
    @endforeach
</tbody>

            </table>
        </div>
    @else
        <p class="alert alert-info">No test results found.</p>
    @endif
</div>

<div class="container-fluid mb-2">
    <hr class="my-1">
   
    <div class="row">
        <div class="col-8">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="thead-text">
                        <span class="d-block mb-2">
    Performed by:
    @if(Auth::check()) <!-- Check if the user is logged in -->
        {{ Auth::user()->name }} <!-- Display the logged-in user's name -->
   
    @endif
</span>
                        </th>
                        <th class="thead-text">
                            <span class="d-block mb-2">Results Entry Date: {{ $patient->created_at->format('m/d/Y') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                {{-- Get the first test result where preview is not null --}}
@php
    $firstResultWithPreview = $testResults->firstWhere('preview', '!==', null);
@endphp

@if ($firstResultWithPreview)
    <tr>
        <td class="thead-text">
            <span class="d-block mb-2">Reviewed by: {{ $firstResultWithPreview->preview }}</span>
        </td>
        <td class="thead-text">
            <span class="d-block mb-2">Date Reviewed: {{ $firstResultWithPreview->created_at->format('m/d/Y') }}</span>
        </td>
    </tr>
@endif


                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

  
<footer>
<div class="logo">   
    <img src="{{ public_path('footer.png') }}" alt="Logo" alt="Logo" style="width:100%; height: auto; ">
      
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>
