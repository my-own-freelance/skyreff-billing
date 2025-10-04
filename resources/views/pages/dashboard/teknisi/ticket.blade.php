@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <link rel="stylesheet" href="{{ asset('/dashboard/css/toggle-status.css') }}">
@endpush
@section('content')
    <div class="row mb-5">
        {{-- TABLE LIST --}}
        <div class="col-md-12" id="boxTable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 class="text-uppercase title">List Tickets</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                    </div>
                    <form class="navbar-left navbar-form mr-md-1 mt-3" id="formFilter">
                        <div class="row">

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
                                <div class="pt-3">
                                    <button class="mt-4 btn btn-sm btn-success mr-3" type="submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="ticketDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Type</th>
                                    <th class="all">Status</th>
                                    <th class="all">Member</th>
                                    <th class="all">Technician</th>
                                    <th class="all">Case</th>
                                    <th class="all">Created</th>
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

    <!-- MODAL DETAIL -->
    <div class="modal fade" id="ticketDetailModal" tabindex="-1" role="dialog" aria-labelledby="ticketDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="ticketDetailModalLabel">Detail Ticket</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Type</th>
                            <td id="detail_type"></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td id="detail_status"></td>
                        </tr>
                        <tr>
                            <th>Member</th>
                            <td id="detail_member"></td>
                        </tr>
                        <tr>
                            <th>Technician</th>
                            <td id="detail_technician"></td>
                        </tr>
                        <tr>
                            <th>Cases</th>
                            <td id="detail_cases"></td>
                        </tr>
                        <tr>
                            <th>Solution</th>
                            <td id="detail_solution"></td>
                        </tr>
                        <tr>
                            <th>Complaint Image</th>
                            <td id="detail_complaint_image"></td>
                        </tr>
                        <tr>
                            <th>Completion Image</th>
                            <td id="detail_completion_image"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Update Progress -->
    <div class="modal fade" id="modalUpdateProgress" tabindex="-1" role="dialog"
        aria-labelledby="modalUpdateProgressLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="formUpdateProgress" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ticket_id" id="ticket_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUpdateProgressLabel">Update Progress Tiket</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Status -->
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="inprogress">In Progress</option>
                                <option value="success">Success</option>
                                <option value="reject">Reject</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>

                        <!-- Solution -->
                        <div class="form-group">
                            <label for="solution">Solusi Penyelesaian</label>
                            <textarea class="form-control" name="solution" id="solution" rows="4"></textarea>
                        </div>

                        <!-- Completion Image -->
                        <div class="form-group" id="completionImageWrapper" style="display:none;">
                            <label for="completion_image">Upload Gambar Completion</label>
                            <input type="file" class="form-control" name="completion_image" id="completion_image"
                                accept="image/*">
                            <small class="form-text text-muted">Hanya jika status sukses. Max 2MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Progress</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
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

        function dataTable(filter) {
            let url = "{{ route('ticket.datatable') }}";
            if (filter) url += "?" + filter;

            dTable = $("#ticketDataTable").DataTable({
                searching: true,
                ordering: true,
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: url,
                columns: [{
                        data: "action"
                    },
                    {
                        data: "type"
                    },
                    {
                        data: "status"
                    },
                    {
                        data: "member_name"
                    },
                    {
                        data: "technician_name"
                    },
                    {
                        data: "cases"
                    },
                    {
                        data: "created_at_formatted"
                    },

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
                member_id: $("#fMember").val()
            }

            dTable.clear();
            dTable.destroy();
            dataTable($.param(dataFilter))
            return false
        })



        function getData(id) {
            $.ajax({
                url: "{{ route('ticket.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                success: function(res) {
                    let d = res.data;
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#boxTable").removeClass("col-md-12").addClass("col-md-7");
                    });
                    $("#id").val(d.id);
                    $("#type").val(d.type).change();
                    $("#status").val(d.status).change();
                    $("#member_id").val(d.member_id).change();
                    $("#technician_id").val(d.technician_id).change();
                    $("#cases").val(d.cases);
                },
                error: function(err) {
                    showMessage("warning", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function showDetail(id) {
            $.ajax({
                url: "{{ route('ticket.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                success: function(res) {
                    let d = res.data;

                    let statusClass = {
                        open: "badge badge-secondary",
                        inprogress: "badge badge-info",
                        success: "badge badge-success",
                        reject: "badge badge-warning",
                        failed: "badge badge-danger"
                    };

                    let statusBadge = `<span class="${statusClass[d.status] ?? 'badge badge-light'}">
                        ${d.status.toUpperCase()}
                    </span>`;


                    $("#detail_type").text(d.type);
                    $("#detail_status").html(statusBadge);
                    $("#detail_member").text(d.member ? d.member.name ?? '-' : '-');
                    $("#detail_technician").text(d.technician ? d.technician.name ?? '-' : '-');
                    $("#detail_cases").text(d.cases ?? '-');
                    $("#detail_solution").text(d.solution ?? '-');

                    if (d.complaint_image) {
                        $("#detail_complaint_image").html(
                            `<a href="/storage/${d.complaint_image}" target="_blank">
                        <img src="/storage/${d.complaint_image}" class="img-fluid rounded" style="max-height:150px">
                    </a>`
                        );
                    } else {
                        $("#detail_complaint_image").html('-');
                    }

                    if (d.completion_image) {
                        $("#detail_completion_image").html(
                            `<a href="/storage/${d.completion_image}" target="_blank">
                        <img src="/storage/${d.completion_image}" class="img-fluid rounded" style="max-height:150px">
                    </a>`
                        );
                    } else {
                        $("#detail_completion_image").html('-');
                    }

                    $("#ticketDetailModal").modal("show");
                },
                error: function(err) {
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }


        function claimTicket(id) {
            let c = confirm("Apakah anda yakin untuk mengambil ticket ini ?");
            if (c) {
                $.ajax({
                    url: "{{ route('ticket.claim') }}",
                    method: "POST",
                    data: {
                        id: id
                    },
                    success: function(res) {
                        refreshData();
                        showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    },
                    error: function(err) {
                        showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                            ?.message);
                    }
                })
            }
        }



        // buka modal
        function openUpdateProgress(ticketId, currentStatus = 'inprogress', currentSolution = '') {
            currentTicketId = ticketId;
            $("#ticket_id").val(ticketId);
            $("#status").val(currentStatus);
            $("#solution").val(currentSolution);
            $("#completion_image").val('');

            // tampilkan field image jika status 'success'
            $("#completionImageWrapper").toggle(currentStatus === 'success');

            $("#modalUpdateProgress").modal("show");
        }

        // toggle input image saat status berubah
        $("#status").on("change", function() {
            $("#completionImageWrapper").toggle($(this).val() === 'success');
        });

        // submit form via AJAX
        $("#formUpdateProgress").on("submit", function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('ticket.process') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status === "success") {
                        refreshData();
                        showMessage("success", "flaticon-alarm-1", "Sukses", res.message);

                        $("#modalUpdateProgress").modal("hide");
                        $("#formUpdateProgress")[0].reset();
                        $('#datatable').DataTable().ajax.reload(null, false);
                    } else {
                        showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                            .responseJSON
                            ?.message);
                    }
                },
                error: function(err) {
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                        .responseJSON
                        ?.message);
                }
            });
        });
    </script>
@endpush
