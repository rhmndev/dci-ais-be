<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
        }

        h1 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f0f0f0;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>New Purchase Order Created</h1>

        <p>Dear [Supplier Name],</p>

        <p>This is to confirm that we have issued a new purchase order (PO) with the following details:</p>

        <table>
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Order Date</th>
                    <th>Delivery Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $purchaseOrder->po_number }}</td>
                    <td>{{ $purchaseOrder->order_date }}</td>
                    <td>{{ $purchaseOrder->delivery_date }}</td>
                </tr>
            </tbody>
        </table>

        <h2>Order Items</h2>

        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchaseOrder->items as $item)
                    <tr>
                        <td>{{ $item->material->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit_price }}</td>
                        <td>{{ $item->quantity * $item->unit_price }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="total">Total Amount:</td>
                    <td class="total">{{ $purchaseOrder->total_amount }}</td>
                </tr>
            </tbody>
        </table>

        <p>Please review the attached purchase order document for complete details.</p>

        <p>Thank you for your prompt attention to this matter.</p>

        <p>Sincerely,<br>
            [Your Company Name]</p>
    </div>
</body>

</html>
