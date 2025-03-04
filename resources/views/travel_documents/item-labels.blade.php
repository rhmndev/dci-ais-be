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
    @if (!$is_all)
    <table class="label-container">
        <tr>
            <td>
                <table class="label-table" style="font-size:8px;">
                    <tr>
                        <td colspan="2" style="text-align: center; padding:15px;"> 
                            <img src="{{ public_path('/img/logo.png') }}" alt="DCI Logo" class="logo">
                        </td>
                        <td style="text-align: right;padding:18px;"> 
                            @isset($itemLabel->qr_path)
                            <img src="{{ public_path('storage/'.$itemLabel->qr_path) }}" alt="QR Code Item" style="width: 50px;" class="qrimage">
                            <br><small style="font-size: 8px; margin-right: 7px;">{{$itemLabel->item_number}}</small>
                            @endisset
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Supplier</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $itemLabel->purchaseOrder->supplier->name ?? 'SUPPLIER_NAME' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Material</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $itemLabel->purchaseOrderItem->material->description ?? 'ITEM_NAME' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Part No.</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $itemLabel->purchaseOrderItem->material->code ?? 'ITEM_NUMBER' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Qty</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $itemLabel->qty ?? 'QTY' }} {{ $itemLabel->purchaseOrderItem->material->unit ?? 'UNIT' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Lot Production</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $itemLabel->lot_production_number ?? 'LOT_PRODUCTION' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Inspector</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $itemLabel->inspector_name ?? 'VERIFIED_BY' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Inspection Date</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        @if ($itemLabel->inspection_date)
                        <td> {{ $itemLabel->inspection_date ?: '' }} </td>
                        @else
                        <td>-</td>
                        @endif
                    </tr>
                    <tr>
                        <td colspan="3" style="padding: 0px; border: 1px solid black;">
                            <table style="width: 100%; border: none;">
                                <tr style="border: none;">
                                    <td class="nested-label" style="border: none;width: 30%; vertical-align: top; text-align: left;">Description</td>
                                    <td class="nested-value" colspan="2" style="border: none;">&nbsp;</td>
                                    <td class="nested-value" style="border: none; border-left: 1px solid black; text-align:center;"><h1>OK</h1></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @else
    <table class="label-container">
        @foreach($itemLabels as $label)
        <tr>
            <td>
                <table class="label-table" style="font-size:8px;">
                    <tr>
                        <td colspan="2" style="text-align: center; padding:15px;"> 
                            <img src="{{ public_path('/img/logo.png') }}" alt="DCI Logo" class="logo">
                        </td>
                        <td style="text-align: right; padding-top:18px;"> 
                            @isset($label->qr_path)
                            <img src="{{ public_path('storage/'.$label->qr_path) }}" alt="QR Code Item" style="width: 50px;" class="qrimage">
                            <br><small style="font-size: 8px; margin-right: 7px;">{{$label->item_number}}</small>
                            @endisset
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Supplier</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $label->purchaseOrder->supplier->name ?? 'SUPPLIER_NAME' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Material</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $label->purchaseOrderItem->material->description ?? 'ITEM_NAME' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Part No.</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $label->purchaseOrderItem->material->code ?? 'ITEM_NUMBER' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Qty</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $label->qty ?? 'QTY' }} {{ $label->purchaseOrderItem->material->unit ?? 'UNIT' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Lot Production</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $label->lot_production_number ?? 'LOT_PRODUCTION' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Inspector</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $label->inspector_name ?? 'VERIFIED_BY' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">Inspection Date</td>
                        <td style="text-align:center; width: 10%;">:</td>
                        <td>{{ $label->inspection_date ? $label->inspection_date : '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding: 0px; border: 1px solid black;">
                            <table style="width: 100%; border: none;">
                                <tr style="border: none;">
                                    <td class="nested-label" style="border: none;width: 30%; vertical-align: top; text-align: left;">Description</td>
                                    <td class="nested-value" colspan="2" style="border: none;">&nbsp;</td>
                                    <td class="nested-value" style="border: none; border-left: 1px solid black; text-align:center;"><h1>OK</h1></td>
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

