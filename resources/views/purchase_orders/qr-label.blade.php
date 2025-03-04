<!DOCTYPE html>
<html>
<head>
    <title>QR Label - {{ $po->po_number }}</title>
</head>
<body>
    <div style="text-align: center;">
        <h1>{{ $po->po_number }}</h1>
        <img src="{{ $qrCode }}" alt="QR Code">
    </div>
</body>
</html>