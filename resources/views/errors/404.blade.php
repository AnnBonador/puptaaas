<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>404</title>

    <link href="https://fonts.googleapis.com/css?family=Kanit:200" rel="stylesheet">

    <link type="text/css" rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}" />

    <link type="text/css" rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />

</head>

<body>

    <div id="notfound">
        <div class="notfound">
            <div class="notfound-404">
                <h1>404</h1>
            </div>
            <h2>Oops! Nothing was found</h2>
            <p>The page you are looking for might have been removed had its name changed or is temporarily unavailable.
                <a href="{{ url('/') }}">Return to homepage</a>
            </p>
        </div>
    </div>

</body>

</html>
