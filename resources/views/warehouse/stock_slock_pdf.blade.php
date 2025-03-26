<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Slock PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* Smaller font size */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 5px; /* Reduced padding for smaller size */
            text-align: left;
            font-size: 9px; /* Smaller text inside table cells */
            word-wrap: break-word; /* Allow long words to wrap */
            max-width: 120px; /* Limit width of the cells */
            text-overflow: ellipsis; /* Add ellipsis for overflowed content */
            white-space: nowrap; /* Prevent text from wrapping */
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        td {
            text-align: left;
        }

        h2 {
            font-size: 14px; /* Smaller title */
        }

        /* Optional: Add page breaks for better pagination in PDF */
        @media print {
            body {
                font-size: 9px; /* Smaller font size for print */
            }
            table {
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <h2>Stock Slock Report (From {{ $stockSlocks->first()->date_income }} to {{ $stockSlocks->last()->date_income }})</h2>

    <table>
        <thead>
            <tr>
                <th>Sloc</th>
                <th>Rack</th>
                <th>Material</th>
                <th>Valuated Stock</th>
                <th>Time</th> 
                <th>Last Time Take In</th>
                <th>Last Time Take Out</th>
                <th>Tag</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stockSlocks as $stockSlock)
                <tr>
                    <td>{{ $stockSlock->slock_code }}</td>
                    <td>{{ $stockSlock->rack_code }}</td>
                    <td>{{ $stockSlock->material_code }}</td>
                    <td>{{ $stockSlock->valuated_stock }} {{ $stockSlock->uom }}</td>
                    <td>{{ $stockSlock->date_income }} {{ $stockSlock->time_income }}</td>
                    <td>{{ $stockSlock->last_time_take_in }}</td>
                    <td>{{ $stockSlock->last_time_take_out }}</td>
                    <td>{{ $stockSlock->tag }}</td>
                    <td>{{ $stockSlock->note }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
