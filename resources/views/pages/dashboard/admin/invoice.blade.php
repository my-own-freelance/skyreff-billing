@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
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
                        <h5 class="text-uppercase title">List Invoice</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                        <button class="btn btn-mini btn-primary" onclick="return addData();">Tambah Invoice</button>
                    </div>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="invoiceDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Invoice Date</th>
                                    <th class="all">Invoice Number</th>
                                    <th class="all">Subscription Number</th>
                                    <th class="all">User</th>
                                    <th class="all">Plan</th>
                                    <th class="all">Amount</th>
                                    <th class="all">Invoice Period</th>
                                    <th class="all">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan=98" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 col-sm-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>Tambah / Edit Invoice</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-sm btn-warning" onclick="return closeForm(this)" id="btnCloseForm">
                            <i class="ion-android-close"></i>
                        </button>
                    </div>
                </div>
                <div class="card-block">
                    <form>
                        <input class="form-control" id="id" type="hidden" name="id" />
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input class="form-control" id="invoice_number" type="text" name="invoice_number"
                                placeholder="Auto Generated" readonly />
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input class="form-control" id="amount" type="number" name="amount"
                                placeholder="Jumlah Invoice" required />
                        </div>
                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input class="form-control" id="due_date" type="date" name="due_date" required />
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="expired">Expired</option>
                                <option value="cancel">Cancel</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-sm btn-primary" type="submit" id="submit">
                                <i class="ti-save"></i><span>Simpan</span>
                            </button>
                            <button class="btn btn-sm btn-default" id="reset" type="reset"
                                style="margin-left : 10px;"><span>Reset</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script>
        let dTable = null;

        $(function() {
            dataTable();
        })

        function dataTable() {
            const url = "{{ route('invoice.datatable') }}";
            dTable = $("#invoiceDataTable").DataTable({
                searching: true,
                ordering: true,
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
                    },
                    {
                        data: "created_at_formatted"
                    },
                    {
                        data: "invoice_number"
                    },
                    {
                        data: "subscription_number"
                    },
                    {
                        data: "user_name"
                    },
                    {
                        data: "plan_name"
                    },
                    {
                        data: "amount"
                    },
                    {
                        data: "invoice_period"
                    },
                    {
                        data: "status"
                    },
                ],
                pageLength: 10,
            });
        }

        function refreshData() {
            dTable.ajax.reload(null, false);
        }

        function addData() {
            $("#formEditable").attr('data-action', 'add').fadeIn(200);
            $("#boxTable").removeClass("col-md-12").addClass("col-md-7");
            $("#invoice_number").focus();
        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-7").addClass("col-md-12");
                $("#reset").click();
            })
        }

        function printInvoice(id) {
            window.open("{{ url('dashboard/invoice/print') }}/" + id, "_blank");
        }

        function updateStatus(id, status) {
            let c = confirm("Apakah anda yakin ingin mengubah status invoice ini menjadi " + status + "?");
            if (c) {
                $.ajax({
                    url: "{{ route('invoice.change-status') }}",
                    method: "POST",
                    data: {
                        id: id,
                        status: status,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        refreshData();
                        showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    },
                    error: function(err) {
                        console.log("error :", err);
                        showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                            ?.message);
                    }
                })
            }
        }

        function printInvoice(id) {
            const url = "{{ route('invoice.print', ':id') }}".replace(':id', id);
            window.open(url, '_blank'); // buka di tab baru
            return false;
        }
    </script>
@endpush
