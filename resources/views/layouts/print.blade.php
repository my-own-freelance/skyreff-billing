<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('/dashboard/css/bootstrap.min.css') }}">

    <title>@yield('title')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        table td,
        table th {
            padding: 5px;
            border: 1px solid #ddd;
        }

        table th {
            background: #f5f5f5;
        }

        h2 {
            margin-top: 0;
        }

        .text-right {
            text-align: right;
        }

        .no-border {
            border: none !important;
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
</head>

<body onload="window.print()">
    <div class="invoice-box">
        @yield('content')
    </div>
</body>

</html>
