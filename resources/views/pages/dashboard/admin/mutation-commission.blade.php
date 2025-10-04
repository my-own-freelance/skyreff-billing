@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <link rel="stylesheet" href="{{ asset('/dashboard/css/toggle-status.css') }}">
    <style>
        .wrap-text {
            max-width: 500px;
            word-wrap: break-word;
            white-space: normal;
        }
    </style>
@endpush
@section('content')
    <div class="row mb-5">
        <div class="col-md-12" id="boxTable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 class="text-uppercase title">List Mutasi</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                    </div>
                    <form class="navbar-left navbar-form mr-md-1 mt-3" id="formFilter">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tanggal Mulai</label>
                                    <input class="form-control date-picker" id="dateFrom" type="text"
                                        placeholder="Pilih tanggal awal" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tanggal Akhir</label>
                                    <input class="form-control date-picker" id="dateTo" type="text"
                                        placeholder="Pilih tanggal akhir" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fType">Filter Tipe</label>
                                    <select class="form-control" id="fType" name="fType">
                                        <option value="">All</option>
                                        <option value="C">Comission</option>
                                        <option value="W">Withdraw</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fTechnician">Filter Teknisi</label>
                                    <select class="form-control" id="fTechnician" name="fTechnician">
                                        <option value="">All</option>
                                        @foreach ($technicians as $teknisi)
                                            <option value="{{ $teknisi->id }}">({{ $teknisi->username }})
                                                {{ $teknisi->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="pt-3">
                                    <button class="mt-4 btn btn-sm btn-success mr-3" type="submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="mutationDataTable">
                            <thead>
                                <tr>
                                    <th class="all">Action</th>
                                    <th class="all">Code</th>
                                    <th class="all">Tipe</th>
                                    <th class="all">Teknisi</th>
                                    <th class="all">Nominal</th>
                                    <th class="">Saldo Awal</th>
                                    <th class="">Saldo Akhir</th>
                                    <th class="">Status</th>
                                    <th class="">Target</th>
                                    <th class="all">Tanggal</th>
                                    <th class="all">Tanggal Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="11" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL REJECT / SUCCESS WITH REASON --}}
    <div class="modal fade" id="modalRejectSuccess" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="formRejectSuccess">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <input type="hidden" name="trxId" id="trxId">
                        <input type="hidden" name="reqStatus" id="reqStatus">
                        <div class="form-group">
                            <label for="reason">Catatan</label>
                            <textarea class="form-control" name="reason" id="reason" cols="30" rows="5" required></textarea>
                        </div>
                        <div class="form-group" id="divProofPayment" style="display: none;">
                            <label for=proofPayment">Bukti Transfer</label>
                            <input class="form-control" id="proofPayment" type="file" name="proofPayment"
                                placeholder="upload gambar" />
                            <small class="text-danger">Max ukuran 10MB</small>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- MODAL DETAIL --}}
    <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="bankTarget"></p>
                    <p id="amount"></p>
                    <p id="admin"></p>
                    <p id="totalAmount"></p>
                    <p id="reasonReject"></p>
                    <img alt="" id="proofImg">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('/dashboard/js/plugin/moment/moment.min.js') }}"></script>
    <script src="{{ asset('/dashboard/js/plugin/datepicker/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/select2/select2.full.min.js') }}"></script>

    <script>
        let dTable = null;

        $("#fTechnician").select2({
            theme: "bootstrap"
        })

        $(function() {
            $('#dateFrom').datetimepicker({
                format: 'DD/MM/YYYY',
            });
            $('#dateTo').datetimepicker({
                format: 'DD/MM/YYYY',
            });
            $("#dateFrom").val(moment().startOf('month').format("DD/MM/YYYY"))
            $("#dateTo").val(moment().endOf('month').format("DD/MM/YYYY"))
            dataTable();
        })

        function dataTable(filter) {
            let url = "{{ route('mutation.datatable') }}";
            if (filter) url += "?" + filter;

            dTable = $("#mutationDataTable").DataTable({
                searching: true,
                orderng: true,
                lengthChange: true,
                responsive: true,
                processing: true,
                serverSide: true,
                searchDelay: 1000,
                paging: true,
                lengthMenu: [5, 10, 25, 50, 100],
                ajax: url,
                columns: [{
                    data: "action"
                }, {
                    data: "code"
                }, {
                    data: "type"
                }, {
                    data: "teknisi"
                }, {
                    data: "amount"
                }, {
                    data: "first_commission",
                }, {
                    data: "last_commission"
                }, {
                    data: "status"
                }, {
                    data: 'target'
                }, {
                    data: "created"
                }, {
                    data: "updated"
                }],
                pageLength: 25,
            });
        }

        function refreshData() {
            dTable.ajax.reload(null, false);
        }

        $('#formFilter').submit(function(e) {
            e.preventDefault()
            let dataFilter = {
                tgl_awal: $("#dateFrom").val(),
                tgl_akhir: $("#dateTo").val(),
                type: $("#fType").val(),
                user_id: $("#fTechnician").val()
            }

            dTable.clear();
            dTable.destroy();
            dataTable($.param(dataFilter))
            return false
        })


        function getData(id, status) {
            $.ajax({
                url: "{{ route('commission.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    let data = res.data;
                    loadModelDetail(data, status);
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("warning", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function loadModelDetail(data, status) {
            const modal = $("#modalDetail");
            modal.modal('show');
            modal.off('shown.bs.modal').on('shown.bs.modal', function() {
                if (status == "DETAIL") {
                    $("#modalDetailTitle").html("DETAIL WITHDRAW")
                    $("#bankTarget").html(`Tujuan : ${data.bank_name} (${data.bank_account})`);
                    $("#amount").html(`Nominal : ${data.amount}`);
                    $("#admin").html(`Admin : ${data.admin}`);
                    $("#totalAmount").html(`Total : ${data.total_amount}`);
                    $("#reasonReject").html(`Catatan : ${data.remark}`);
                }

                if (status == "SHOW-REASON-REJECT") {
                    $("#modalDetailTitle").html("ALASAN WITHDRAW DITOLAK")
                    $("#reasonReject").html(data.remark);
                }

                if (status == "SHOW-PROOF-PAYMENT") {
                    $("#modalDetailTitle").html("BUKTI PEMBAYARAN TRANSFER")
                    $("#bankTarget").html(`TUJUAN : ${data.bank_name} (${data.bank_account})`);
                    $("#amount").html(`Nominal : ${data.amount}`);
                    $("#admin").html(`Admin : ${data.admin}`);
                    $("#totalAmount").html(`Total : ${data.total_amount}`);
                    $("#reasonReject").html(`Catatan : ${data.remark}`);
                    if (data.proof_of_payment) {
                        $("#proofImg").attr("src", data.proof_of_payment).attr("width", "100%");
                    }
                }
            });

            return false;
        }

        $("#modalDetail").on("hidden.bs.modal", function() {
            $("#reasonReject").html("");
            $("#bankTarget").html("");
            $("#amount").html("");
            $("#admin").html("");
            $("#totalAmount").html("");
            $("#proofImg").attr("src", "");
        });

        function changeStatus(id, status) {
            if (status == "REJECT" || status == "SUCCESS") {
                laodModalRejectSuccess(id, status);
                return false;
            } else {
                let c = confirm(`Anda yakin untuk mengubah status transaksi menjadi ${status} ?`)
                if (c) {
                    let dataToSend = new FormData();
                    dataToSend.append("id", id);
                    dataToSend.append("status", status);
                    sendChangeStatus(dataToSend)
                }
                return false;
            }
        }

        function laodModalRejectSuccess(id, status) {
            const modal = $("#modalRejectSuccess");
            modal.modal('show');
            modal.off('shown.bs.modal').on('shown.bs.modal', function() {
                $("#trxId").val(id);
                $("#reqStatus").val(status);
                if (status == "SUCCESS") {
                    $("#divProofPayment").fadeIn(200, function() {
                        $("#proofPayment").attr("required", true);
                    })
                }
            });

            return false;
        }

        $("#formRejectSuccess").submit(function(e) {
            e.preventDefault();
            let c = confirm(`Anda yakin untuk mengubah status transaksi menjadi ${$("#reqStatus").val()} ?`)
            if (c) {
                let dataToSend = new FormData();
                dataToSend.append("id", $("#trxId").val());
                dataToSend.append("status", $("#reqStatus").val());
                dataToSend.append("remark", $("#reason").val());

                if ($("#reqStatus").val() == "SUCCESS") {
                    dataToSend.append("proof_of_payment", document.getElementById("proofPayment").files[0]);
                }
                sendChangeStatus(dataToSend, true) // hide modal after action
            }
            return false;
        })

        $("#modalRejectSuccess").on("hidden.bs.modal", function() {
            $(this).find("form")[0].reset();
            // BY DEFAULT UPLOAD BUKTI TRANSFER DI HIDDEN
            $("#divProofPayment").slideUp(200, function() {
                $("#proofPayment").attr("required", false);
            });
        });


        function sendChangeStatus(data, hideModal = false) {
            $.ajax({
                url: "{{ route('commission.change-status') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                    if (hideModal) {
                        $("#modalRejectSuccess").modal('hide')
                    };
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                        .responseJSON
                        ?.message);
                }
            })
        }

    </script>
@endpush
