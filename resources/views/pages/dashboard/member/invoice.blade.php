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

        function printInvoice(id) {
            const url = "{{ route('invoice.print', ':id') }}".replace(':id', id);
            window.open(url, '_blank'); // buka di tab baru
            return false;
        }
    </script>
@endpush
