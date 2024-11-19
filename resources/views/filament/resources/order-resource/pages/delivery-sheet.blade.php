<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Sheet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 5px;
            background-color: #fff;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Delivery Sheet</h1>
        <table>
            <thead>
                <tr>
                    <th>Order Barcode</th>
                    <th>Barcode Image</th>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>City</th>
                    <th>Zone</th>
                    <th>Cash Required</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>{{ $order->barcode }}</td>
                    <td><img src="{{ $order->barcode_image }}" alt="Barcode" width="100" /></td>
                    <td>{{ $order->customer->name }}</td>
                    <td>{{ $order->customer->phone_number }}</td>
                    <td>{{ $order->city->name }}</td>
                    <td>{{ $order->zone->name }}</td>
                    <td>{{ $order->cash_required }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
