<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Salary slip - {{ $salaryMonth->format('F Y') }}</title>
    <style>
        body {
            color: #18221e;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            border-bottom: 2px solid #244b3b;
            margin-bottom: 32px;
            padding-bottom: 16px;
        }

        .brand {
            color: #244b3b;
            font-size: 22px;
            font-weight: bold;
        }

        .title {
            font-size: 18px;
            margin-top: 20px;
        }

        .muted {
            color: #58645c;
        }

        table {
            border-collapse: collapse;
            margin-top: 28px;
            width: 100%;
        }

        th,
        td {
            border-bottom: 1px solid #dce2dc;
            padding: 12px 8px;
            text-align: left;
        }

        th:last-child,
        td:last-child {
            text-align: right;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
        }

        .footer {
            border-top: 1px solid #dce2dc;
            color: #58645c;
            margin-top: 36px;
            padding-top: 12px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="brand">branch</div>
        <div class="title">Salary slip</div>
        <div class="muted">Pay period: {{ $salaryMonth->format('F Y') }}</div>
    </header>

    <section>
        <div><strong>Employee:</strong> {{ $user->name }}</div>
        <div><strong>Work email:</strong> {{ $user->email }}</div>
    </section>

    <table>
        <thead>
            <tr>
                <th scope="col">Description</th>
                <th scope="col">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Fixed monthly salary</td>
                <td>₹{{ number_format((float) $user->monthly_salary, 2) }}</td>
            </tr>
            <tr class="total">
                <td>Total monthly salary</td>
                <td>₹{{ number_format((float) $user->monthly_salary, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <footer class="footer">
        Generated on {{ now()->format('F j, Y') }}. This slip reflects the fixed monthly salary configured in Branch; allowances and deductions are not managed in this app.
    </footer>
</body>
</html>
