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
        <div class="col-md-12">
            <div class="card card-primary text-white">
                <div class="card-body">
                    <h1 class="fw-bold">PAKET </h1>
                    <h5 class="op-8">Jenis paket yang aktif untuk akun reseller anda adalah </h5>

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
