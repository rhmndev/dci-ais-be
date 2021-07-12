<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
    
<div class="visible-print text-center">
	<h1>Laravel - QR Code Generator Example</h1>
     
    {!! base64_encode(QrCode::size(48)->generate('202008101113Y')); !!}
     
    <p>example</p>
</div>
    
</body>
</html>