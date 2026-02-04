<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Note - Order #{{ $Order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 18px;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            padding: 5px 0;
        }
        .info-value {
            display: table-cell;
            width: 70%;
            padding: 5px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        .total-row {
            margin: 5px 0;
        }
        .total-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        .signature-section {
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            margin: 30px 0 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DELIVERY NOTE</h1>
        <h2>Order #{{ $Order->id }}</h2>
    </div>

    <div class="info-section">
        <h3>Order Information</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Order ID:</div>
                <div class="info-value">{{ $Order->id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date:</div>
                <div class="info-value">{{ $Order->created_at->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Delivery Date:</div>
                <div class="info-value">{{ $Order->delivery_date ? \Carbon\Carbon::parse($Order->delivery_date)->format('d/m/Y') : 'Not set' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Site Code:</div>
                <div class="info-value">{{ $Order->site_code }}</div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h3>Company Information</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Company Name:</div>
                <div class="info-value">{{ $Order->Company->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Contact Person:</div>
                <div class="info-value">{{ $Order->Company->contact_person ?? 'Not specified' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">{{ $Order->Company->phone ?? 'Not specified' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $Order->Company->email ?? 'Not specified' }}</div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h3>Driver Information</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Driver Name:</div>
                <div class="info-value">{{ $Order->driver_name ?? 'Not specified' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Driver Phone:</div>
                <div class="info-value">{{ $Order->driver_phone ?? 'Not specified' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Vehicle Number:</div>
                <div class="info-value">{{ $Order->vehicle_number ?? 'Not specified' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">License Plate:</div>
                <div class="info-value">{{ $Order->license_plate ?? 'Not specified' }}</div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h3>Items to be Delivered</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Product Name</th>
                    <th>Series Number</th>
                    <th>Unit Price</th>
                    <th>Duration</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($Order->OrderItems as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->ProductItem->product->name }}</td>
                    <td>{{ $item->ProductItem->series_number }}</td>
                    <td>${{ number_format($item->unit_price ?? 0, 2) }}</td>
                    <td>{{ $item->duration_days ?? 0 }} days</td>
                    <td>${{ number_format($item->total_price ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="total-section">
        <div class="total-row">
            <span class="total-label">Total Items:</span>
            <span>{{ $Order->OrderItems->count() }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Total Amount:</span>
            <span>${{ number_format($Order->OrderItems->sum('total_price'), 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Driver Signature</div>
                <div>Date: _______________</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Customer Signature</div>
                <div>Date: _______________</div>
            </div>
        </div>
    </div>
</body>
</html>
