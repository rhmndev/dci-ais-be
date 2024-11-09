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
            /* page-break-after: always; Page break after each table */
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
            width: 250px;
        }

        .label-table td {
            padding: 5px;
        }

    </style>
</head>
<body>
    @if ($is_all)
    <table class="label-container">
        
    </table>
    @else
    <table class="label-container">
        @foreach($itemLabels as $label)
        <tr>
            <td>
                <table class="label-table">
                    <tr>
                        <td style="text-align: center; padding:15px;"> 
                            <img src="{{ public_path('/img/logo.png') }}" alt="DCI Logo" class="logo">
                        </td>
                        <td style="text-align: right;padding:15px;"> 
                            @isset($label->qr_path)
                            <img src="{{ public_path('storage/'.$label->qr_path) }}" alt="QR Code Item" style="width: 50px;" class="qrimage">
                            <br><small style="font-size: 10px;">{{$label->item_number}}</small>
                            @endisset
                        </td>
                    </tr>
                    <tr>
                        <td>Supplier:</td>
                        <td>{{ $label->purchaseOrder->supplier->name ?? 'SUPPLIER_NAME' }}</td>
                    </tr>
                    <tr>
                        <td>Part Name:</td>
                        <td>{{ $label->purchaseOrderItem->material->description ?? 'ITEM_NAME' }}</td>
                    </tr>
                    <tr>
                        <td>Part Number:</td>
                        <td>{{ $label->purchaseOrderItem->material->code ?? 'ITEM_NUMBER' }}</td>
                    </tr>
                    <tr>
                        <td>Qty:</td>
                        <td>{{ $label->qty ?? 'QTY' }} {{ $label->purchaseOrderItem->material->unit ?? 'UNIT' }}</td>
                    </tr>
                    <tr>
                        <td>Lot Production:</td>
                        <td>{{ $label->lot_production_number ?? 'LOT_PRODUCTION' }}</td>
                    </tr>
                    <tr>
                        <td>Verified By:</td>
                        <td>{{ $label->inspector_name ?? 'VERIFIED_BY' }}</td>
                    </tr>
                    <tr>
                        <td>Date:</td>
                        {{-- <td>{{ $label->inspection_date ? $label->inspection_date->format('Y-m-d') : '-' }}</td> --}}
                    </tr>
                </table>
            </td>
        </tr>
        @endforeach
    </table>
    @endif
</body>
</html>

