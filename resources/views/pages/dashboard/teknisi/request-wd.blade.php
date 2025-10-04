@extends('layouts.dashboard')
@section('title', $title)

@section('content')
    <div class="row mb-5">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body" id="formWd">
                    <form>
                        <input type="hidden" class="form-control" id="fId" placeholder="fId">
                        <div class="form-group">
                            <label for="nominal">Nominal Withdraw</label>
                            <input type="number" class="form-control" id="nominal" min="" required
                                placeholder="Input Nominal Withdraw">
                            <small class="ml-2 text-info">Nominal belum termasuk biaya admin transfer bank.</small>
                        </div>
                        <div class="form-group">
                            <label for="bankName">Nama Bank</label>
                            <input type="text" class="form-control" id="bankName" required value="{{ $bank_type }}"
                                placeholder="Input Nama Bank Anda">
                        </div>
                        <div class="form-group">
                            <label for="bankAccount">No Rekening</label>
                            <input type="text" class="form-control" id="bankAccount" required value="{{ $bank_account }}"
                                placeholder="Input No Rekening Anda">
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Notes</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" cols="30" rows="6"></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-success pull-right m-2">Submit
                            Withdraw</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-info background_primary">
                <div class="card-body">
                    <h1 class="display-4 text-center" style="font-size: 50px;">
                        <b> Rp. {{ number_format($balance, 0, ',', '.') }} </b>
                    </h1>
                    <h4 class="mt-3 pb-2 mb-5 fw-bold text-center">Estimasi Saldo Anda</h4>
                    <h4 class="mt-5 pb-3 mb-0 fw-bold">Ketentuan Withdraw:</h4>
                    <ol>
                        <li>
                            <h6>Minimal Withdraw = Rp. 100.000</h6>
                        </li>
                        <li>
                            <h6>Maximal Withdraw = Rp. 1.000.000</h6>
                        </li>
                        <li>
                            <h6>Komisi Anda = Rp. {{ number_format($balance, 0, ',', '.') }} </h6>
                        </li>
                        <li>
                            <h6>Admin Bank Transfer = Rp. 6.500</h6>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $('#formWd form').submit(function() {
            let dataSend = {
                amount: $('#nominal').val(),
                bank_name: $('#bankName').val(),
                bank_account: $('#bankAccount').val(),
                notes: $('#keterangan').val()
            }

            $.ajax({
                url: "{{ route('commission.create') }}",
                method: 'POST',
                header: {
                    'Content-Type': 'application/json'
                },
                data: dataSend,
                beforeSend: function() {
                    console.log('Loading...')
                },
                success: function(res) {
                    showMessage('success', 'flaticon-alarm-1', 'Sucess !', res.message)
                    setTimeout(() => {
                        window.location.href = "{{ route('mutation') }}"
                    }, 3000)
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("warning", "flaticon-error", "Peringatan", err.message || err
                        .responseJSON
                        ?.message);
                }
            })

            return false
        })
    </script>
@endpush
