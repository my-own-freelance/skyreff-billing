@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <style>
        .card_content_wrapper {
            width: 220px;
        }
    </style>
@endpush
@section('content')
    <div class="row mt--2">
        <div class="col-md-4 col-lg-4">
            <div class="card">
                <div class="card-body p-3 text-center">
                    <div class="row">
                        <div class="col-md-8 card_content_wrapper mx-auto">
                            <h2 class="mt-2"><b class="text2_primary" style="font-weight: 900;">SALDO KOMISI</b></h2>
                            <h1 class="text-primary"><i class="fas fa-money-bill-wave" style="font-size: 230%;"></i></h1>
                            <h4><b style="font-size:150%;" id="w3_balance">{{ $data['commission'] }}</b></h4>
                            <div class="text-muted">Penarikan Tersedia</div>
                        </div>
                    </div>
                    <div class="separator-dashed"></div>
                    <h4><b style="font-size:150%;" id="w3_wd_pending">{{ $data['wd_commission'] }}</b></h4>
                    <div class="text-muted">Penarikan Menunggu Konfirmasi
                        <a class="btn btn-icon btn-link" href="{{ route('mutation') }}" type="button">
                            <i class="fa fa-external-link-alt"></i>
                        </a>
                    </div>
                    <div class="separator-dashed"></div>
                    <a class="btn btn-primary text-white btn-block" href="{{ route('commission.request-wd') }}"> Tarik
                        Saldo Komisi</a>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-sm-12">
            <div class="row">

                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-body bubble-shadow">
                            <h1 class="mt-4">{{ $data['month_commission'] }}</h1>
                            <h3 class="mt-3">Komisi Bulan Ini</h3>
                            <div class="pull-right">
                                <a class="text-white" href="{{ route('mutation') }}">
                                    <small class="fw-bold op-9">Cek Mutasi<i
                                            class="fas fa-external-link-alt ml-2"></i></small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script></script>
@endpush
