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
                                    <label for="fSortBy">Sorting By</label>
                                    <select class="form-control" id="fSortBy" name="fSortBy">
                                        <option value="created_at">Date Invoice</option>
                                        <option value="due_date">Date Expired</option>
                                        <option value="paid_at">Date Payment</option>
                                        <option value="invoice_number">Inovice Number</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fSortType">Sorting Type</label>
                                    <select class="form-control" id="fSortType" name="fSortType">
                                        <option value="desc">Desc</option>
                                        <option value="asc">Asc</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fFilterStatus">Filter Status</label>
                                    <select class="form-control" id="fFilterStatus" name="fFilterStatus">
                                        <option value="">All</option>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="paid">Paid</option>
                                        <option value="expired">Expired</option>
                                        <option value="cancel">Cancel</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fMember">Filter Member</label>
                                    <select class="form-control" id="fMember" name="fMember">
                                        <option value="">All</option>
                                        @foreach ($members as $member)
                                            <option value="{{ $member->id }}">({{ $member->username }})
                                                {{ $member->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fFilterPlan">Filter Plan</label>
                                    <select class="form-control" id="fFilterPlan" name="fFilterPlan">
                                        <option value="">All</option>
                                        @foreach ($plans as $plan)
                                            <option value="{{ $plan->id }}">
                                                {{ $plan->name }}
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
                                    <th class="all">Expired Date</th>
                                    <th class="all">Payment Date</th>
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
@endsection

@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('/dashboard/js/plugin/moment/moment.min.js') }}"></script>
    <script src="{{ asset('/dashboard/js/plugin/datepicker/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        let dTable = null;

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
            let url = "{{ route('invoice.datatable') }}";
            if (filter) url += "?" + filter;

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
                    {
                        data: "due_date_formatted"
                    },
                    {
                        data: "paid_at_formatted"
                    }
                ],
                pageLength: 10,
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
                sort_by: $("#fSortBy").val(),
                sort_type: $("#fSortType").val(),
                status: $("#fFilterStatus").val(),
                user_id: $("#fMember").val(),
                plan_id: $("#fFilterPlan").val()
            }

            dTable.clear();
            dTable.destroy();
            dataTable($.param(dataFilter))
            return false
        })

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
