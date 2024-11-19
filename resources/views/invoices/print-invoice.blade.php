<!-- resources/views/invoices/print-invoice.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        /* General styling */
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #4a90e2;
        }

        .header,
        .footer {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Invoice Info */
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .invoice-info div {
            width: 48%;
        }

        .invoice-info p {
            margin: 4px 0;
        }

        /* Orders Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #4a90e2;
            color: #fff;
        }

        /* Footer */
        .total-amount {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <h1>Invoice #{{ $invoice->id }}</h1>
        <div class="header">
            <p><strong>Date:</strong> {{ $invoice->invoice_date }}</p>
            <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
        </div>

        <!-- Invoice Information -->
        <div class="invoice-info">
            <!-- Beneficiary Information -->
            <div>
                <h3>Beneficiary</h3>
                <p><strong>Name:</strong> {{ $invoice->beneficiary->name }}</p>
                <p><strong>Type:</strong> {{ ucfirst($invoice->beneficiary_type) }}</p>
            </div>

            <!-- Invoice Details -->
            <div>
                <h3>Invoice Details</h3>
                <p><strong>Invoice Type:</strong> {{ ucfirst($invoice->type) }}</p>
                <p><strong>Total Amount:</strong> ${{ number_format($invoice->amount, 2) }}</p>
            </div>
        </div>

        <!-- Orders Table -->
        <h3>Orders</h3>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Barcode</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                        <td>{{ $order->barcode }}</td>
                        <td>${{ number_format($order->cash_required, 2) }}</td>
                        <td>{{ $order->order_status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total Amount -->
        <div class="total-amount">
            <p>Total Amount: ${{ number_format($invoice->amount, 2) }}</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Cliq @2024</p>
        </div>
    </div>
</body>
<script>
    window.print(); // Automatically print the invoice when the page loads. Replace this line with your desired print logic.
    window.onafterprint = () => window.close(); // Close the tab after printing
</script>
</html>
