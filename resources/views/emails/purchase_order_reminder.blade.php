<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order Reminder</title>
</head>
<body>
    <h1>Purchase Order Reminder</h1>

    <p>This is a reminder that Purchase Order {{ $purchaseOrder->po_number }}.</p>

    <p>Please review and take the necessary actions.</p>

    <ul>
        <li><strong>PO Number:</strong> {{ $purchaseOrder->po_number }}</li>
        <li><strong>Order Date:</strong> {{ $purchaseOrder->order_date }}</li>
        <li><strong>Supplier:</strong> {{ $purchaseOrder->supplier->name }}</li>
        <li><strong>Status:</strong> {{ $purchaseOrder->status }}</li>
    </ul>

    <p>Thank you for your attention to this matter.</p>
</body>
</html>
