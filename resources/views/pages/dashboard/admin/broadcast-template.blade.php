@extends('layouts.dashboard')
@section('title', $title)

@push('styles')
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12" id="templateView">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Edit Template</div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <form id="templateForm" class="form-group" data-action="edit">
                                <input class="form-control" id="fId" type="hidden" />

                                <div class="mb-3">
                                    <label for="tipeTemplate">Tipe Template</label>
                                    <select id="tipeTemplate" name="" class="form-control">
                                        @foreach ($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3 position-relative">
                                    <label for="content">Text</label>
                                    <button type="button" class="position-absolute btn btn-icon btn-link"
                                        style="z-index:100; right:17px; top:120px" onclick="copyText('#content')">
                                        <i class="fas fa-copy text-muted"></i>
                                    </button>
                                    <textarea id="content"
                                        style="border-color:#ebedf2; padding:0.6rem 1rem; font-size:14px; display:block; width:100%; height:30rem; resize:vertical; overflow:auto;"></textarea>
                                </div>

                                <div class="mb-0">
                                    <button class="btn btn-sm btn-success mr-2" id="saveForm"
                                        type="submit">Simpan</button>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-5 border-left">
                            <div class="px-3 pt-3">
                                <h3><b><i class="fas fa-exclamation-circle mr-2"></i> INFORMASI</b></h3>
                            </div>

                            <div class="px-3">
                                <div class="separator-solid"></div>
                                <h5 class="text-muted">
                                    Untuk data variabel konstan gunakan sesuai dengan kode yang sudah diatur pada list
                                    di bawah ini, pilih yang akan digunakan sesuai template anda:
                                </h5>
                                <ul class="m-0 p-0 mt-2" style="list-style-type:none;">
                                    <li><b class="mr-2">Pra Register</b></li>
                                    <li><b class="mr-2">#member_name# :</b> Nama lengkap member</li>
                                    <li><b class="mr-2">#company_name# :</b> Judul Web yg ada di setting</li>
                                    <li><b class="mr-2">#support_contact# :</b> Telpon yg ada di setting</li>

                                    <br>
                                    <li><b class="mr-2">Aktivasi Paket</b></li>
                                    <li><b class="mr-2">#member_name# :</b> Nama lengkap member</li>
                                    <li><b class="mr-2">#plan_name# :</b> Nama Paket Plan</li>
                                    <li><b class="mr-2">#plan_price# :</b> Harga Paket Plan</li>
                                    <li><b class="mr-2">#subscription_number# :</b> Nomor Subscription</li>
                                    <li><b class="mr-2">#company_name# :</b> Judul Web yg ada di setting</li>
                                    <li><b class="mr-2">#support_contact# :</b> Telpon yg ada di setting</li>

                                    <br>
                                    <li><b class="mr-2">Invoice Sukses Bayar</b></li>
                                    <li><b class="mr-2">#member_name# :</b> Nama lengkap member</li>
                                    <li><b class="mr-2">#invoice_number# :</b> Nomor Invoice</li>
                                    <li><b class="mr-2">#plan_name# :</b> Nama Paket Plan</li>
                                    <li><b class="mr-2">#invoice_amount# :</b> Total Bayar</li>
                                    <li><b class="mr-2">#period# :</b> Periode Langganan</li>
                                    <li><b class="mr-2">#company_name# :</b> Judul Web yg ada di setting</li>
                                    <li><b class="mr-2">#support_contact# :</b> Telpon yg ada di setting</li>

                                    <br>
                                    <li><b class="mr-2">Invoice Baru Keluar</b></li>
                                    <li><b class="mr-2">#member_name# :</b> Nama lengkap member</li>
                                    <li><b class="mr-2">#invoice_number# :</b> Nomor Invoice</li>
                                    <li><b class="mr-2">#plan_name# :</b> Nama Paket Plan</li>
                                    <li><b class="mr-2">#invoice_amount# :</b> Total Bayar</li>
                                    <li><b class="mr-2">#period# :</b> Periode Langganan</li>
                                    <li><b class="mr-2">#invoice_due_date# :</b> Tgl Jatuh Tempo</li>
                                    <li><b class="mr-2">#company_name# :</b> Judul Web yg ada di setting</li>
                                    <li><b class="mr-2">#support_contact# :</b> Telpon yg ada di setting</li>

                                    <br>
                                    <li><b class="mr-2">Invoice Expired</b></li>
                                    <li><b class="mr-2">#member_name# :</b> Nama lengkap member</li>
                                    <li><b class="mr-2">#invoice_number# :</b> Nomor Invoice</li>
                                    <li><b class="mr-2">#plan_name# :</b> Nama Paket Plan</li>
                                    <li><b class="mr-2">#invoice_amount# :</b> Total Bayar</li>
                                    <li><b class="mr-2">#invoice_due_date# :</b> Tgl Jatuh Tempo</li>
                                    <li><b class="mr-2">#invoice_due_date# :</b> Tgl Jatuh Tempo</li>
                                    <li><b class="mr-2">#company_name# :</b> Judul Web yg ada di setting</li>
                                    <li><b class="mr-2">#support_contact# :</b> Telpon yg ada di setting</li>

                                    <br>
                                    <li><b class="mr-2">Teknisi [Pemasangan Member Baru]</b></li>
                                    <li><b class="mr-2">#technician_name# :</b> Nama Teknisi</li>
                                    <li><b class="mr-2">#member_name# :</b> Nama lengkap member</li>
                                    <li><b class="mr-2">#member_phone# :</b> Telpon member</li>
                                    <li><b class="mr-2">#member_address# :</b> Alamat member</li>
                                    <li><b class="mr-2">#member_maps# :</b> Maps member</li>
                                    <li><b class="mr-2">#company_name# :</b> Judul Web yg ada di setting</li>
                                    <li><b class="mr-2">#support_contact# :</b> Telpon yg ada di setting</li>

                                    <br>
                                    <li><b class="mr-2">Tiket Teknisi</b></li>
                                    <li><b class="mr-2">#technician_name# :</b> Nama Teknisi</li>
                                    <li><b class="mr-2">#ticket_type# :</b> Tipe Tiket</li>
                                    <li><b class="mr-2">#ticket_cases# :</b> Kasus Tiket</li>
                                    <li><b class="mr-2">#member_name# :</b> Nama lengkap member</li>
                                    <li><b class="mr-2">#member_phone# :</b> Telpon member</li>
                                    <br>
                                </ul>
                            </div>

                            <div class="px-3 pb-3 pt-2">
                                <h5>Gunakan <b>*Contoh*</b> agar text tebal / bold</h5>
                                <h5>Gunakan <i>_Contoh_</i> agar text miring / italic</h5>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            let id = $('#tipeTemplate').val();
            if (id) {
                getData(id);
            }
        });

        $('#tipeTemplate').on('change', function() {
            getData(this.value);
        });

        function getData(id) {
            $.ajax({
                url: "{{ route('broadcast-template.detail', ['id' => ':id']) }}".replace(':id', id),
                method: 'GET',
                header: {
                    'Content-Type': 'application/json'
                },
                success: function(msg) {
                    let d = msg.data;
                    $('#fId').val(d.id);
                    $('#content').val(d.content);
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            });
        }

        function copyText(elem) {
            var text = $(`${elem}`).val()

            var successful = navigator.clipboard.writeText(text)
            if (successful) {
                showMessage('success', 'flaticon-success', 'Success !', 'Script tersalin.');
            }
            return false
        }

        $('#templateForm').submit(function() {
            let action = $('#templateForm').attr('data-action');
            let dataToSend = {
                id: $('#fId').val(),
                name: $('#tipeTemplate').find('option:selected').text(),
                content: $('#content').val()
            }
            saveData(dataToSend)
            return false
        })

        function saveData(data) {
            $.ajax({
                url: "{{ route('broadcast-template.update') }}",
                method: 'POST',
                data: data,
                header: {
                    'Content-Type': 'application/json'
                },
                beforeSend: function() {
                    console.log('Sending data ...!')
                },
                success: function(msg) {
                    showMessage('success', 'flaticon-alarm-1', 'Success !', msg.message)
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }
    </script>
@endpush
