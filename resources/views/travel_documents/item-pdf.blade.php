<!DOCTYPE html>
<html>
<head>
    <title>Print Label</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
        }

        .label-container {
            width: 49%; /* Two labels per row */
            height: auto;
            border: 1px solid #000;
            padding: 10px;
            margin: 5px; /* Adjust margin for spacing */
            float: left;
            page-break-inside: avoid; /* Avoid page break inside label container */
        }

        .label-container:nth-child(even)::after { 
            content: "";
            clear: both;
            display: table; 
        }

        /* .label-container:after {
            content: '';
            display: block;
            border-top: 2px dashed #000;
            margin-top: 10px;
        } */

        .logo {
            display: block; /* Allow the logo to occupy full width */
            margin: 0 auto 10px; /* Center the logo */
            width: 100px;
        }

        .label-table {
            width: 100%;
            border-collapse: collapse;
        }

        .label-table td {
            padding: 5px;
        }

        .label-table td:first-child {
            font-weight: bold;
        }

        .label-container:nth-child(4n) {
            page-break-after: always; /* Force page break after every 4th label */
        }
    </style>
</head>
<body>
    @if ($is_all)
    @foreach ($travelDocument->items as $tdItem)    
    <div class="label-container">
        <table class="label-table">
            <tr>
                <td style="text-align: center;"> 
                    <img src="{{ public_path('/img/logo.png') }}" alt="DCI Logo" class="logo">
                </td>
                <td style="text-align: right;"> 
                    @isset($tdItem->qr_path)
                    <img src="{{ public_path('storage/'.$tdItem->qr_path) }}" alt="QR Code Item" style="width: 80px;" class="qrimage">
                    @endisset
                </td>
            </tr>
            <tr>
                <td>Supplier:</td>
                <td>{{ $tdItem->travelDocument->supplier->name ?? 'SUPPLIER_NAME' }}</td>
            </tr>
            <tr>
                <td>Part Name:</td>
                <td>{{ $tdItem->poItem->material->description ?? 'ITEM_NAME' }}</td>
            </tr>
            <tr>
                <td>Part Number:</td>
                <td>{{ $tdItem->poItem->material->code ?? 'ITEM_NUMBER' }}</td>
            </tr>
            <tr>
                <td>Qty:</td>
                <td>{{ $tdItem->qty ?? 'QTY' }} {{ $tdItem->poItem->material->unit ?? 'UNIT' }}</td>
            </tr>
            <tr>
                <td>Lot Production:</td>
                <td>{{ $tdItem->lot_production_number ?? 'LOT_PRODUCTION' }}</td>
            </tr>
            <tr>
                <td>Verified By:</td>
                <td>{{ $tdItem->verified_by ?? 'VERIFIED_BY' }}</td>
            </tr>
            <tr>
                <td>Date:</td>
                <td>{{ $tdItem->created_at ? $tdItem->created_at->format('Y-m-d') : 'DATE' }}</td>
            </tr>
        </table>
    </div>
    @endforeach
    @else
    <div class="label-container">
        <table class="label-table">
            <tr>
                <td style="text-align: center;"> 
                    <img src="{{ public_path('/img/logo.png') }}" alt="DCI Logo" class="logo">
                </td>
                <td style="text-align: right;"> 
                    @isset($item->qr_path)
                    <img src="{{ public_path('storage/'.$item->qr_path) }}" alt="QR Code Item" style="width: 80px;" class="qrimage">
                    @endisset
                </td>
            </tr>
            <tr>
                <td>Supplier:</td>
                <td>{{ $item->travelDocument->supplier->name ?? 'SUPPLIER_NAME' }}</td>
            </tr>
            <tr>
                <td>Part Name:</td>
                <td>{{ $item->poItem->material->description ?? 'ITEM_NAME' }}</td>
            </tr>
            <tr>
                <td>Part Number:</td>
                <td>{{ $item->poItem->material->code ?? 'ITEM_NUMBER' }}</td>
            </tr>
            <tr>
                <td>Qty:</td>
                <td>{{ $item->qty ?? 'QTY' }} {{ $item->poItem->material->unit ?? 'UNIT' }}</td>
            </tr>
            <tr>
                <td>Lot Production:</td>
                <td>{{ $item->lot_production_number ?? 'LOT_PRODUCTION' }}</td>
            </tr>
            <tr>
                <td>Verified By:</td>
                <td>{{ $item->verified_by ?? 'VERIFIED_BY' }}</td>
            </tr>
            <tr>
                <td>Date:</td>
                <td>{{ $item->created_at ? $item->created_at->format('Y-m-d') : 'DATE' }}</td>
            </tr>
        </table>
    </div>
    @endif
</body>
</html>

