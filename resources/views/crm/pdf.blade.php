<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        dt { font-weight: bold; margin-top: 8px; }
        dd { margin: 0; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <dl>
        @foreach ($fields as $label => $value)
            <dt>{{ $label }}</dt>
            <dd>{{ $value }}</dd>
        @endforeach
    </dl>
</body>
</html>
