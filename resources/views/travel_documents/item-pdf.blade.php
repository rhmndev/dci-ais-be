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
            width: 80mm;
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
            font-size: 10px;
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
            width: 180px;
        }

        .colon-cell {
            width: 2px; /* Adjust width as needed */
            text-align: center; /* Optional: Center the colon */
        }
        .nested-label {
            width: 30%; /* Adjust as needed */
        }

        .nested-value {
            width: 70%; /* Adjust as needed */
        }
    </style>
</head>
<body>
    @if ($is_all)
    <table class="label-container">
        @for ($i = 0; $i < count($travelDocument->items); $i++)
        <tr>
            <td>
                <table class="label-table" style="font-size:8px;">
                    <tr>
                        <td colspan="2" style="text-align: center; padding:15px;"> 
                            <img src="{{ public_path('/img/logo.png') }}" alt="DCI Logo" class="logo">
                        </td>
                        <td style="text-align: right; padding-top:18px;"> 
                            @isset($travelDocument->items[$i]->qr_path)
                            <img src="{{ public_path('storage/'.$travelDocument->items[$i]->qr_path) }}" alt="QR Code Item" style="width: 50px;" class="qrimage">
                            <br><small style="font-size: 8px; margin-right: 7px;">{{$travelDocument->items[$i]->qr_tdi_no}}</small>
                            @endisset
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Supplier</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $travelDocument->items[$i]->travelDocument->supplier->name ?? 'SUPPLIER_NAME' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Material</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $travelDocument->items[$i]->poItem->material->description ?? 'ITEM_NAME' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Part No.</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $travelDocument->items[$i]->poItem->material->code ?? 'ITEM_NUMBER' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Qty</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $travelDocument->items[$i]->qty ?? 'QTY' }} {{ $travelDocument->items[$i]->poItem->material->unit ?? 'UNIT' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Lot Production</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $travelDocument->items[$i]->lot_production_number ?? 'LOT_PRODUCTION' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Inspector</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $travelDocument->items[$i]->tempLabelItem->inspector_name ?? 'VERIFIED_BY' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Inspection Date</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>
                            @if ($travelDocument->items[$i]->tempLabelItem)
                            {{ $travelDocument->items[$i]->tempLabelItem->inspection_date ? $travelDocument->items[$i]->tempLabelItem->inspection_date : 'DATE' }}                            
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding: 0px; border: 1px solid black;">
                            <table style="width: 100%; border: none;">
                                <tr style="border: none;">
                                    <td class="nested-label" style="border: none;width: 30%; vertical-align: top; text-align: left;">Description</td>
                                    <td class="nested-value" style="border: none;">&nbsp;</td>
                                    <td class="nested-value" style="border: none; border-left: 1px solid black;text-align: center;">Judgement</td>
                                    <td class="nested-value" style="border: none; border-left: 1px dotted black; text-align:center;"><h1>OK</h1></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endfor
    </table>
    @else
    <table class="label-container">
        @foreach($items as $item)
        <tr>
            <td>
                <table class="label-table" style="font-size:8px;">
                    <tr>
                        <td colspan="2" style="text-align: center; padding:15px;"> 
                            <img src="{{ public_path('/img/logo.png') }}" alt="DCI Logo" class="logo">
                        </td>
                        <td style="text-align: right; padding-top:18px;"> 
                            @isset($item->qr_path)
                            <img src="{{ public_path('storage/'.$item->qr_path) }}" alt="QR Code Item" style="width: 50px;" class="qrimage">
                            <br><span style="font-size: 8px; margin-right: 7px;">{{$item->qr_tdi_no}}</span>
                            @endisset
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Supplier</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $item->travelDocument->supplier->name ?? 'SUPPLIER_NAME' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Material</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $item->poItem->material->description ?? 'ITEM_NAME' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Part No.</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $item->poItem->material->code ?? 'ITEM_NUMBER' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Qty</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $item->qty ?? 'QTY' }} {{ $item->poItem->material->unit ?? 'UNIT' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Lot Production</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $item->lot_production_number ?? 'LOT_PRODUCTION' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Inspector</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $item->tempLabelItem->inspector_name ?? 'VERIFIED_BY' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Inspection Date</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $item->tempLabelItem->inspection_date ? $item->tempLabelItem->inspection_date : 'DATE' }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding: 0px; border: 1px solid black;">
                            <table style="width: 100%; border: none;">
                                <tr style="border: none;">
                                    <td class="nested-label" style="border: none;width: 30%; vertical-align: top; text-align: left;">Description</td>
                                    <td class="nested-value" style="border: none;">&nbsp;</td>
                                    <td class="nested-value" style="border: none; border-left: 1px solid black;text-align: center;">Judgement</td>
                                    <td class="nested-value" style="border: none; border-left: 1px dotted black; text-align:center;"><h1>OK</h1></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endforeach
    </table>
    @endif
</body>
</html>

