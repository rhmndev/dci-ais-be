<!DOCTYPE html>
<html>
<head>
    <title>Travel Document and Labels</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
        }
        .label-container {
            page-break-after: always; /* Page break after each label type */
        }
        .label-table {
            width: 80mm; /* Adjust as needed */
            border: 1px solid #000;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px; /* Adjust as needed */
        }
        /* ... (other styles from item-labels.blade.php and item-package-labels.blade.php) */
        .page-break {
            page-break-after: always;
        }

    </style>
</head>
<body>
    <div class="label-container">
        @include('travel_documents.pdf', ['travelDocument' => $travelDocument])
        <div class="page-break"></div>
    </div>

    <div class="label-container"> {{-- Item Labels Section --}}
        <h2>Item Labels</h2>
        @foreach ($itemLabels as $itemLabel)
            @include('travel_documents.item-labels', ['itemLabel' => $itemLabel, 'is_all' => false])
        @endforeach
        <div class="page-break"></div>

    </div>

    <div class="label-container"> {{-- Package Labels Section --}}
        <h2>Package Labels</h2>
        @foreach($groupedItems as $item)
        @foreach ($item['items']->first()->packageLabel as $packageLabel)
        @include('travel_documents.item-package-labels', ['itemLabel' => $packageLabel, 'is_all' => true])
        @endforeach
        @endforeach

    </div>
</body>
</html>

