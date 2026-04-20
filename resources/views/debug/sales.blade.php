<!DOCTYPE html>
<html>
<head>
    <title>Debug Sales</title>
</head>
<body>

    <h1>Sales Debug</h1>

    @foreach($sales as $sale)
        <p>
            Sale #{{ $sale->id }}
            — Client: {{ $sale->client->name }}
            — Total: {{ $sale->total_amount }}
        </p>
    @endforeach

</body>
</html>