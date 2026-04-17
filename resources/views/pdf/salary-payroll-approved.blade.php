<!DOCTYPE html>
<html>
<head>
    <title>Approved Payroll</title>
    <style>
        body { font-family: Arial; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>

<h2>Approved Payroll</h2>

<p>
Cutoff: {{ $cutoff->name }} <br>
</p>

<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th>Total Earnings</th>
            <th>Total Deduction</th>
            <th>Net Pay</th>
        </tr>
    </thead>

    <tbody>
        @foreach($payrolls as $p)
        <tr>
            <td>{{ $p->user->first_name }} {{ $p->user->last_name }}</td>
            <td>{{ $p->total_earning }}</td>
            <td>{{ $p->total_deduction }}</td>
            <td>{{ $p->total_home_pay }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
