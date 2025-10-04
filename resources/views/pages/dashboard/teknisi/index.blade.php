@php
    use Carbon\Carbon;

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

        .summary-card {
            min-height: 150px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .ticket-status {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
        }

        .ticket-open {
            background-color: #6c757d;
        }

        .ticket-inprogress {
            background-color: #17a2b8;
        }

        .ticket-success {
            background-color: #28a745;
        }

        .ticket-reject {
            background-color: #ffc107;
        }

        .ticket-failed {
            background-color: #dc3545;
        }

        a.btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
@endpush

@section('content')
    <div class="row g-3 mt-3">

        {{-- Saldo Komisi --}}
        <div class="col-md-4">
            <div class="card shadow-sm text-white h-100"
                style="border-radius: 15px; background: linear-gradient(135deg,#667eea,#764ba2);">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Saldo Komisi</h6>
                        <i class="fas fa-wallet fa-2x"></i>
                    </div>
                    <h2 class="display-5 fw-bold mb-2"><span class="counter">{{ $data['commission'] }}</span></h2>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small>Penarikan Tersedia</small>
                        <a href="{{ route('commission.request-wd') }}"
                            class="btn btn-light btn-sm rounded-pill px-4 d-flex align-items-center shadow-sm"
                            style="font-weight: 600; color: #764ba2; background: linear-gradient(135deg,#ffecd2,#fcb69f); transition: all 0.3s;">
                            <i class="fas fa-arrow-up me-2"></i> Tarik
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Komisi Bulan Ini --}}
        <div class="col-md-4">
            <div class="card shadow-sm text-white h-100"
                style="border-radius: 15px; background: linear-gradient(135deg,#f7971e,#ffd200);">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Komisi Bulan Ini</h6>
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                    <h2 class="display-5 fw-bold mb-2"><span class="counter">{{ $data['month_commission'] }}</span></h2>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small>
                            <a href="{{ route('mutation') }}" class="text-white text-decoration-underline">
                                Cek Mutasi <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        </small>

                    </div>
                </div>
            </div>
        </div>

        {{-- Penarikan Menunggu --}}
        <div class="col-md-4">
            <div class="card shadow-sm text-white h-100"
                style="border-radius: 15px; background: linear-gradient(135deg,#ff6a00,#ee0979);">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Penarikan Menunggu</h6>
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                    <h2 class="display-5 fw-bold mb-2"><span class="counter">{{ $data['wd_commission'] }}</span></h2>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small>
                            <a href="{{ route('mutation') }}" class="text-white text-decoration-underline">
                                Cek Mutasi <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        </small>

                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Tiket Terbaru --}}
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tiket Terbaru</h5>
                    <a href="{{ route('ticket') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-2">
                    @if ($data['tickets']->isEmpty())
                        <p class="text-muted">Belum ada tiket.</p>
                    @else
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Member</th>
                                    <th>Kasus</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['tickets'] as $ticket)
                                    <tr>
                                        <td>{{ $ticket->type }}</td>
                                        <td>{{ $ticket->member ? $ticket->member->name : '-' }}</td>
                                        <td>{{ $ticket->cases }}</td>
                                        <td>
                                            {!! getTicketStatusClass($ticket->status) !!}
                                        </td>
                                        <td>{{ Carbon::parse($ticket->created_at)->timezone('Asia/Jakarta') }}
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Optional: add some animation on card hover
            $('.summary-card').hover(function() {
                $(this).addClass('shadow-lg');
            }, function() {
                $(this).removeClass('shadow-lg');
            });
        });

        // Counter animation
        $('.counter').each(function() {
            $(this).prop('Counter', 0).animate({
                Counter: parseInt($(this).text().replace(/\D/g, ''))
            }, {
                duration: 1000,
                easing: 'swing',
                step: function(now) {
                    $(this).text(Math.ceil(now).toLocaleString('id-ID'));
                }
            });
        });
    </script>
@endpush
