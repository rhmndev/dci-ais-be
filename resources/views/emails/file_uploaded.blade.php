<!DOCTYPE html>
<html>
<head>
    <title>New File Uploaded</title>
</head>
<body>
    <h1>New File Uploaded</h1>
    <p>A new file, {{ $fileName }}, has been uploaded by {{ $uploadedBy->name }}.</p>
    <p>You can access the file <a href="{{ url('/') }}">here</a>.</p>
</body>
</html>
