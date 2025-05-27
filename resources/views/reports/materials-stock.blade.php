<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 0;
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 9px;
                color: #666;
            }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        .header {
            margin-bottom: 10px;
        }
        .header-content {
            display: flex;
            align-items: center;
            margin-bottom: 2px;
        }
        .header-logo {
            width: 80px;
            height: auto;
            margin-right: 15px;
        }
        .header-text {
            flex-grow: 1;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
        }
        .header p {
            margin: 1px 0;
            color: #666;
            font-size: 8px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
            font-size: 9px;
        }
        th {
            background-color: #4a5568;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 8px;
            color: #666;
        }
        .number-column {
            width: 30px;
            text-align: center;
        }
        .changed-column {
            width: 80px;
        }
        .stock-column {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <img src="{{ public_path('img/logo.png') }}" class="header-logo" alt="Company Logo">
            <div class="header-text">
                <h1>{{ $title }}</h1>
            </div>
        </div>
        <p>Generated on: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="number-column">No.</th>
                <th>ID SAP</th>
                <th>Material Description</th>
                <th>Min</th>
                <th>Max</th>
                <th class="stock-column">Stock</th>
                <th>Actual Stock</th>
                <th class="changed-column">Changed Min Stock</th>
                <th class="changed-column">Changed Max Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materials as $index => $material)
            <tr>
                <td class="number-column">{{ $index + 1 }}</td>
                <td>{{ $material->code }}</td>
                <td>{{ $material->description }}</td>
                <td>{{ $material->minQty ? $material->minQty . ' ' . $material->unit : '' }}</td>
                <td>{{ $material->maxQty ? $material->maxQty . ' ' . $material->unit : '' }}</td>
                <td class="stock-column">{{ $material->quantity }} {{ $material->unit }}</td>
                <td></td>
                <td class="changed-column"></td>
                <td class="changed-column"></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Printed by: {{ auth()->user()->full_name }}</p>
    </div>
</body>
</html> 