<!DOCTYPE html>
<html>
<head>
    <title>QR Codes</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            font-size: 14px;
            width: 20%;
        }
        th {
            background-color: #f4f4f4;
        }
        img {
            width: 100px;
            height: 100px;
        }
        h1 {
            text-align: center;
            font-size: 20px;
        }
        .no-qr {
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>QR Codes for Racks</h1>
    <table>
        <tbody>
            @php
                $chunks = array_chunk($qrCodes, 5);
            @endphp
            @foreach ($chunks as $row)
                <tr>
                    @foreach ($row as $qr)
                        <td>
                            <strong>{{ $qr['code'] }}</strong><br /><br />
                            @if (isset($qr['qrcode']))
                                <img src="{{ $qr['qrcode'] }}" alt="QR Code">
                            @else
                                <span class="no-qr">No QR Code Available</span>
                            @endif
                        </td>
                    @endforeach
                    @if (count($row) < 5)
                        @for ($i = 0; $i < 5 - count($row); $i++)
                            <td></td>
                        @endfor
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>