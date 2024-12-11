<!-- resources/views/filament/pages/order-details.blade.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .barcode {
            text-align: left;
        }

        .barcode-number {
            text-align: right;
        }

        .details {
            border: 1px solid #ccc; /* Border around the details section */
            border-radius: 5px;
            padding: 10px; /* Padding inside the details section */
            margin-top: 20px;
        }

        .details div {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #ccc; /* Border between each entry */
        }

        .label {
            font-weight: bold;
            width: 40%; /* Adjust width for labels */
        }

        .value {
            width: 60%; /* Adjust width for values */
        }

        /* Remove the last border-bottom for the last entry */
        .details div:last-child {
            border-bottom: none;
        }
        footer {page-break-after: always;}
    </style>
</head>

<body>
    @for($i = 0; $i< $order->number_of_pieces ; $i++)
    <div class="container">

        <div class="header">
            <div class="barcode">
                <img src="{{ $order->barcode_image }}" alt="Barcode" style="width: 150px;" />
            </div>
            <div class="barcode-number">
                <strong>Barcode:</strong> {{ $order->barcode }}
            </div>
        </div>

        <h2>Order Information</h2>
        <div class="details">
            <div>
                <span class="label">Order ID:</span>
                <span class="value">{{ $order->id }}</span>
            </div>
            <div>
                <span class="label">Customer Name:</span>
                <span class="value">{{ $order->customer->name }}</span>
            </div>
            <div>
                <span class="label">Customer Phone:</span>
                <span class="value">{{ $order->customer->phone }}</span>
            </div>
            <div>
                <span class="label">Customer City:</span>
                <span class="value">{{ $order->customer->city->name }}</span>
            </div>
            <div>
                <span class="label">Zone:</span>
                <span class="value">{{ $order->additional_details }}</span>
            </div>
          

            <div>
                <span class="label">Customer additional details:</span>
                <span class="value">{{ $order->customer->additional_details }} </span>
            </div>
            <div>
                <span class="label">Order Description:</span>
                <span class="value">{{ $order->description }}</span>
            </div>
            <div>
                <span class="label">Order Notes:</span>
                <span class="value">{{ $order->order_notes }}</span>
            </div>
            <div>
                <span class="label">Weight (kg):</span>
                <span class="value">{{ $order->weight }}</span>
            </div>
            <div>
                <span class="label">Number of Pieces:</span>
                <span class="value">{{ $order->number_of_pieces }}</span>
            </div>
            <div>
                <span class="label">Invoice Number:</span>
                <span class="value">{{ $order->invoice_number }}</span>
            </div>
            <div>
                <span class="label">Invoice Value:</span>
                <span class="value">{{ $order->invoice_value }}</span>
            </div>
            <div>
                <span class="label">Cash Required:</span>
                <span class="value">{{ $order->cash_required }}</span>
            </div>
            <div>
                <span class="label">Order Date:</span>
                <span class="value">{{ $order->created_at->format('Y-m-d H:i') }}</span>
            </div>
        </div>

        <footer>
{{ $i + 1 }} of {{ $order->number_of_pieces }}
        </footer>
    </div>
    @endfor
</body>
<script>
    window.print(); // Automatically print the invoice when the page loads. Replace this line with your desired print logic.
    window.onafterprint = () => window.close(); // Close the tab after printing
</script>
</html>
