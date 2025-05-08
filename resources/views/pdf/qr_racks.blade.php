<!DOCTYPE html>
<html>
<head>
    <title>QR Codes</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        img {
            width: 150px;
            height: 150px;
        }
    </style>
</head>
<body>
    <h1>QR Codes for Racks</h1>
    <table>
        <thead>
            <tr>
                <th>Rack</th>
                <th>QR Code</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($qrCodes as $qr)
                <tr>
                    <td>{{ $qr['code'] }}</td>
                    @if (isset($qr['qrcode']))
                        <td><img src="{{ $qr['qrcode'] }}" alt="QR Code"></td>
                    @else
                        <td></td>
                    @endif
                </tr>
                <!-- stop -->
            @endforeach
        </tbody>
    </table>
</body>
</html>