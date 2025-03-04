<!DOCTYPE html>
<html>
<head>
    <title>Schedule Delivery Revision Needed</title>
</head>
<body>
    <p>A revision is needed for the schedule delivery of PO {{ $emailData['po_number'] }} from supplier {{ $emailData['supplier'] }}.</p>
    <p><strong>Revision Notes:</strong></p>
    <p>{{ $emailData['revision_notes'] }}</p> 
    <p>Please take necessary action.</p>  
</body>
</html>