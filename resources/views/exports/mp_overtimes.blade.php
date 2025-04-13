<!DOCTYPE html>
<html>
<head>
    <title>Manpower Overtime Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 6px; text-align: center; }
        h3 { text-align: center; }
    </style>
</head>
<body>
    <h3>Manpower Overtime Report</h3>
    <p>Period: {{ $start_date }} to {{ $end_date }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Dept Code</th>
                <th>Date</th>
                <th>Shift</th>
                <th>Place</th>
                <th>Total MP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($overtimes as $index => $ot)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $ot->dept_code }}</td>
                    <td>{{ $ot->date }}</td>
                    <td>{{ $ot->shift_code }}</td>
                    <td>{{ $ot->place_code }}</td>
                    <td>{{ $ot->total_mp }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
