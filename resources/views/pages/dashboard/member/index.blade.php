@php
    use Carbon\Carbon;
    function getStatusLabelInvoice(string $status): string
    {
        switch ($status) {
            case 'paid':
                return '<span class="badge badge-success">Paid</span>';
            case 'unpaid':
                return '<span class="badge badge-warning">Unpaid</span>';
            case 'expired':
                return '<span class="badge badge-danger">Expired</span>';
            case 'cancel':
                return '<span class="badge badge-secondary">Cancel</span>';
            default:
                return '<span class="badge badge-light">Unknown</span>';
        }
    }

    function getTicketStatusClass(string $status): string
    {
        switch ($status) {
            case 'open':
                return '<span class="badge badge-secondary">Open</span>';
            case 'inprogress':
                return '<span class="badge badge-info">InProgress</span>';
            case 'success':
                return '<span class="badge badge-success">Success</span>';
            case 'reject':
                return '<span class="badge badge-warning">Reject</span>';
            case 'failed':
                return '<span class="badge badge-danger">Failed</span>';
            default:
                return '<span class="badge badge-light">Unknown</span>';
        }
    }
@endphp
@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <style>
        .card_content_wrapper {
            width: 220px;
        }

        .stat-card {
            border-radius: 15px;
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-card .icon {
            font-size: 30px;
            opacity: 0.7;
        }

        .table thead {
            background-color: #f8f9fa;
        }
    </style>
@endpush
@section('content')
    <div class="row mt-3">
        {{-- Quick Stats --}}
        <div class="col-md-3">
            <div class="stat-card bg-primary">
                <div>
                    <h6>Subscription</h6>
                    <h4>{{ $data['subscription']->plan->name ?? '-' }}</h4>
                    <small>Status: {{ $data['subscription']->status ?? 'N/A' }}</small>
                </div>
                <i class="fa fa-globe icon"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card bg-success">
                <div>
                    <h6>Devices</h6>
                    <h4>{{ $data['totDevice'] }}</h4>
                    <small>Terdaftar</small>
                </div>
                <i class="fa fa-laptop icon"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card bg-warning">
                <div>
                    <h6>Invoices</h6>
                    <h4>{{ $data['totInvoiceUnpaid'] }}</h4>
                    <small>Belum Lunas</small>
                </div>
                <i class="fa fa-file-invoice-dollar icon"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card bg-danger">
                <div>
                    <h6>Tiket Keluhan</h6>
                    <h4>{{ $data['totOpenTicket'] }}</h4>
                    <small>Open</small>
                </div>
                <i class="fa fa-ticket-alt icon"></i>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        {{-- Invoice Terbaru --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Invoice Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['currentInvoices']->take(5) as $invoice)
                                <tr>
                                    <td>#{{ $invoice->invoice_number }}</td>
                                    <td>{{ Carbon::parse($invoice->created_at)->timezone('Asia/Jakarta') }}
                                    </td>
                                    <td>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                    <td>
                                        {!! getStatusLabelInvoice($invoice->status) !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada invoice</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tiket Keluhan Terbaru --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Tiket Keluhan Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Case</th>
                                <th>Status</th>
                                <th>Teknisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['tickets']->take(5) as $ticket)
                                <tr>
                                    <td>{{ $ticket->type }}</td>
                                    <td>{{ $ticket->cases }}</td>
                                    <td>
                                        {!! getTicketStatusClass($ticket->status) !!}
                                    </td>
                                    <td>{{ $ticket->technician ? $ticket->technician->name : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada tiket</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Device --}}
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Device Terdaftar</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama Device</th>
                                <th>Kutipan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['devices'] as $device)
                                <tr>
                                    <td>
                                        <img src="{{ Storage::url($device->image) }}" width="100"
                                            class="img-fluid rounded" alt="{{ $device->name }}">
                                    </td>
                                    <td>{{ $device->name }}</td>
                                    <td>{{ $device->excerpt }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada device</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        // LOAD NOTIFIKASI DARI ADMIN
        (function loadNotification() {
            let data = {!! json_encode($data['announcements']) !!};
            $.each(data, function(index, item) {
                setTimeout(() => {
                    let content = {
                        title: item.subject,
                        message: item.message,
                        icon: 'fa fa-bell',
                        // url: "/",
                    }

                    let state = "info"
                    switch (item.type) {
                        case "P":
                            state = "primary";
                            break;
                        case "I":
                            state = "info";
                            break;
                        case "S":
                            state = "success";
                            break;
                        case "W":
                            state = "warning";
                            break;
                        case "D":
                            state = "danger";
                            break;
                    }

                    $.notify(content, {
                        type: state,
                        placement: {
                            from: "top",
                            align: "right"
                        },
                        time: 10000,
                        // delay: 0,
                    });
                }, index * 2000);
            });
        })()
    </script>
@endpush
