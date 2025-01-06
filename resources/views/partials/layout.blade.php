<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield ("title") | OurSpace</title>

    <link rel="icon" href="/resources/img/favicon.ico" type="image/x-icon">

    @vite(["resources/css/app.css"])
</head>
<body>

    <div class="master-container">
        @include ("partials.header")

        <main>
            @if (session ("success"))
                <div class="success">
                    {{ session ("success") }}
                </div>
            @endif

            @if (session ("error"))
                <div class="error">
                    {{ session ("error") }}
                </div>
            @endif

            @if (session ("info"))
                <div class="info">
                    {{ session ("info") }}
                </div>
            @endif

            @yield ("content")
        </main>

        @include ("partials.footer")
    </div>

</body>
</html>
