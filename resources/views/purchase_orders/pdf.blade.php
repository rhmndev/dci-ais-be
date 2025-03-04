<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        body {
            font-family: sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Purchase Order - {{ $purchaseOrder->po_number }}</h1>

    <h2>Vendor Details:</h2>
    <p><strong>Name:</strong> {{ $purchaseOrder->supplier->name }}</p> 
    <p><strong>Address:</strong> {{ $purchaseOrder->supplier->address }}</p> 
    <p><strong>Email:</strong> {{ $purchaseOrder->supplier->email }}</p> 

    <h2>Order Details:</h2>
    <p><strong>Order Date:</strong> {{ $purchaseOrder->order_date }}</p>
    <p><strong>Delivery Date:</strong> {{ $purchaseOrder->delivery_date }}</p>

    <h2>Items:</h2>
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
            @foreach($purchaseOrder->items as $item)
                <tr>
                    <td>{{ $item->material->description }}</td> 
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->unit_price }}</td>
                    <td>{{ $item->quantity * $item->unit_price }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Total Amount:</strong> {{ $purchaseOrder->total_amount }}</p>
</body>
</html>
