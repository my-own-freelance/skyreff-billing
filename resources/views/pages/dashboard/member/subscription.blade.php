@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <link rel="stylesheet" href="{{ asset('/dashboard/css/toggle-status.css') }}">
@endpush
@section('content')
    <div class="row mb-5">
        <div class="col-md-12" id="boxTable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 class="text-uppercase title">DATA SUBSCRIPTION</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                    </div>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="subscriptionDataTable">
                            <thead>
                                <tr>
                                    <th class="all">Subscription Number</th>
                                    <th class="all">Member</th>
                                    <th class="all">Plan</th>
                                    <th class="all">Tipe</th>
                                    <th class="all">Identifier</th>
                                    <th class="all">Status</th>
                                    <th class="all">Periode</th>
                                    <th class="all">Next Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center"><small>Tidak Ada Data</small></td>
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
        });

        function dataTable() {
            const url = "{{ route('subscription.datatable') }}";
            dTable = $("#subscriptionDataTable").DataTable({
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
                columns: [
                    {
                        data: "subscription_number"
                    },
                    {
                        data: "member_name"
                    },
                    {
                        data: "plan_name"
                    },
                    {
                        data: "type"
                    },
                    {
                        data: "identifier"
                    },
                    {
                        data: "status"
                    },
                    {
                        data: "current_period"
                    },
                    {
                        data: "next_invoice"
                    }
                ],
                pageLength: 25,
            });
        }

        function refreshData() {
            dTable.ajax.reload(null, false);
        }

    </script>
@endpush
