<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order Escalation Reminder</title>
</head>
<body>
    <h1>Purchase Order Escalation Reminder</h1>

    <p>Dear HOD,</p>

    <p>This is to inform you that Purchase Order <strong>{{ $purchaseOrder->po_number }}</strong> has been pending for 7 days and requires your attention.</p>

    <p>Please review the purchase order details in the system and take appropriate action.</p>

    <ul>
        <li><strong>PO Number:</strong> {{ $purchaseOrder->po_number }}</li>
        <li><strong>Status:</strong> {{ $purchaseOrder->status }}</li>
        <li><strong>Created At:</strong> {{ $purchaseOrder->created_at->format('Y-m-d H:i:s') }}</li>
        <!-- Add more relevant details as needed -->
    </ul>

    <p>Thank you for your prompt attention to this matter.</p>

    <p>Sincerely,<br>
    The Procurement Team</p>
</body>
</html>
