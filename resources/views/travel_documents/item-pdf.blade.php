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
            /* width: 150mm; */
            page-break-after: always; /* Page break after each table */
            /* padding: 5px; */
        }
        
        .label-table {
            width: 120mm;
            border: 1px solid #000;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .label-table tr:first-child {
            font-weight: bold;
            border: 1px solid #000;
        }
        
        .label-table tr :first-child{
            border:none;
        }
        .label-table tr td {
            padding: 2px;
            border-top: 1px dotted #000;
            border-bottom: 1px dotted #000;
        }
        .label-row {
            display: table-row; /* Ensure each label acts like a table row */
        }

        .label-cell {
            display: table-cell; /* Ensure each label part acts like a cell */
            width: 50%; /* Two labels per row */
            border: 1px solid #000;
            padding: 10px;
            margin: 5px;
            vertical-align: top; /* Align content to the top of the cell */
        }

        .logo {
            display: block;
            margin: 0 auto 10px;
            width: 100px;
        }

        .label-table td {
            padding: 5px;
        }

    </style>
</head>
<body>
    @if ($is_all)
    <table class="label-container">
        @for ($i = 0; $i < count($travelDocument->items); $i++)
        <tr>
            <td>
                <table class="label-table">
                    <tr>
                        <td style="text-align: center;"> 
                            <img src="{{ public_path('/img/logo.png') }}" alt="DCI Logo" class="logo">
                        </td>
                        <td style="text-align: right;"> 
                            @isset($travelDocument->items[$i]->qr_path)
                            <img src="{{ public_path('storage/'.$travelDocument->items[$i]->qr_path) }}" alt="QR Code Item" style="width: 80px;" class="qrimage">
                            @endisset
                        </td>
                    </tr>
                    <tr>
                        <td>Supplier:</td>
                        <td>{{ $travelDocument->items[$i]->travelDocument->supplier->name ?? 'SUPPLIER_NAME' }}</td>
                    </tr>
                    <tr>
                        <td>Part Name:</td>
                        <td>{{ $travelDocument->items[$i]->poItem->material->description ?? 'ITEM_NAME' }}</td>
                    </tr>
                    <tr>
                        <td>Part Number:</td>
                        <td>{{ $travelDocument->items[$i]->poItem->material->code ?? 'ITEM_NUMBER' }}</td>
                    </tr>
                    <tr>
                        <td>Qty:</td>
                        <td>{{ $travelDocument->items[$i]->qty ?? 'QTY' }} {{ $travelDocument->items[$i]->poItem->material->unit ?? 'UNIT' }}</td>
                    </tr>
                    <tr>
                        <td>Lot Production:</td>
                        <td>{{ $travelDocument->items[$i]->lot_production_number ?? 'LOT_PRODUCTION' }}</td>
                    </tr>
                    <tr>
                        <td>Verified By:</td>
                        <td>{{ $travelDocument->items[$i]->verified_by ?? 'VERIFIED_BY' }}</td>
                    </tr>
                    <tr>
                        <td>Date:</td>
                        <td>{{ $travelDocument->items[$i]->created_at ? $travelDocument->items[$i]->created_at->format('Y-m-d') : 'DATE' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        @endfor
    </table>
    @else
    <div class="label-container">
        <table class="label-table">
            <tr>
                <td style="text-align: center; padding:5px;"> 
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

