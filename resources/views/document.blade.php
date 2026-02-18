<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @foreach($headTags as $tag)
        {!! $tag !!}
    @endforeach
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { width: {{ $width }}px; height: {{ $height }}px; overflow: hidden; }
    </style>
</head>
<body>{!! $html !!}</body>
</html>
