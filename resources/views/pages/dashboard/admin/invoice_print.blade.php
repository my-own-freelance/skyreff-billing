@extends('layouts.print')
@section('title', 'Invoice #' . $invoice->invoice_number)

@push('styles')
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 14px;
        }

        .invoice-box {
            max-width: 850px;
            margin: auto;
            padding: 30px;
            border: 1px solid #f0f0f0;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .invoice-box header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .invoice-box header img {
            max-height: 60px;
        }

        .invoice-box h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }

        .invoice-box table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .invoice-box table td,
        .invoice-box table th {
            padding: 10px;
            vertical-align: top;
        }

        .invoice-box table th {
            background: #f9f9f9;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
            text-align: left;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.total td {
            border-top: 2px solid #ddd;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: bold;
            color: #fff;
        }

        .status-paid {
            background-color: #28a745;
        }

        .status-unpaid {
            background-color: #ffc107;
            color: #212529;
        }

        .status-expired {
            background-color: #dc3545;
        }

        .status-cancel {
            background-color: #6c757d;
        }

        .invoice-footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #888;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .invoice-box {
                box-shadow: none;
                border: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="invoice-box">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <!-- Logo di kiri -->
            <div>
                <img src="{{ asset('skyreff-logo.jpeg') }}" alt="Logo" style="height: 60px;">
            </div>

            <!-- Judul invoice di kanan -->
            <div style="text-align: right;">
                <h1 style="margin: 0; font-size: 28px;">INVOICE</h1>
                <p style="margin: 0; font-size: 16px; color: #555;">#{{ $invoice->invoice_number }}</p>
            </div>
        </header>


        <table>
            <tr>
                <td>
                    <strong>Invoice Date:</strong> {{ $invoice->created_at->format('Y-m-d') }}<br>
                    <strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}
                </td>
                <td class="text-right">
                    <strong>Status:</strong>
                    @php
                        $statusClass = match ($invoice->status) {
                            'paid' => 'status-paid',
                            'unpaid' => 'status-unpaid',
                            'expired' => 'status-expired',
                            'cancel' => 'status-cancel',
                        };
                    @endphp
                    <span class="status {{ $statusClass }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <th>Bill To</th>
                <th>Subscription Info</th>
            </tr>
            <tr>
                <td>
                    <strong>{{ $invoice->user->name ?? '-' }}</strong><br>
                    {{ $invoice->user->phone ?? '-' }}<br>
                    {{ $invoice->user->address ?? '-' }}
                </td>
                <td>
                    <strong>Subscription #: </strong>{{ $invoice->subscription->subscription_number ?? '-' }}<br>
                    <strong>Type:</strong> {{ $invoice->subscription->type ?? '-' }}<br>
                    <strong>Plan:</strong> {{ $invoice->plan->name ?? '-' }}<br>
                    <strong>Period:</strong>
                    {{ $invoice->invoice_period_start ? $invoice->invoice_period_start->format('Y-m-d') : '-' }}
                    s/d
                    {{ $invoice->invoice_period_end ? $invoice->invoice_period_end->format('Y-m-d') : '-' }}
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
            <tr class="item">
                <td>{{ $invoice->plan->name ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total">
                <td>Total</td>
                <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="invoice-footer mt-4">
            Thank you for your business!<br>
            Powered by SkyReff
        </div>
    </div>

    <script>
        window.print();
    </script>
@endsection
