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
                        <button class="btn btn-mini btn-primary" onclick="return addData();">Tambah Ticket</button>
                    </div>
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

        {{-- FORM CREATE / EDIT --}}
        <div class="col-md-5 col-sm-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>Tambah / Edit Ticket</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-sm btn-warning" onclick="return closeForm(this)" id="btnCloseForm">
                            <i class="ion-android-close"></i>
                        </button>
                    </div>
                </div>
                <div class="card-block">
                    <form enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">

                        <div class="form-group">
                            <label for="type">Type Ticket</label>
                            <select class="form-control" name="type" id="type" required>
                                <option value="">Pilih Type</option>
                                <option value="gangguan">Gangguan</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="pemasangan">Pemasangan</option>
                                <option value="troubleshoot">Troubleshoot</option>
                                <option value="lain-lain">Lain-lain</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status">Status Ticket</label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="open">Open</option>
                                <option value="inprogress">In Progress</option>
                                <option value="success">Success</option>
                                <option value="reject">Reject</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="member_id">Assign Member</label>
                            <select class="form-control" name="member_id" id="member_id">
                                <option value="">-- Optional --</option>
                                @foreach ($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="technician_id">Assign Technician</label>
                            <select class="form-control" name="technician_id" id="technician_id">
                                <option value="">-- Optional --</option>
                                @foreach ($technicians as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cases">Cases</label>
                            <textarea class="form-control" name="cases" id="cases" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="complaint_image">Complaint Image</label>
                            <input type="file" class="form-control" id="complaint_image" name="complaint_image">
                            <small class="text-danger">Max 2MB</small>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-sm btn-primary" type="submit" id="submit">
                                <i class="ti-save"></i> Simpan
                            </button>
                            <button class="btn btn-sm btn-default" type="reset" id="reset" style="margin-left:10px;">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL -->
    <div class="modal fade" id="ticketDetailModal" tabindex="-1" role="dialog"
        aria-labelledby="ticketDetailModalLabel" aria-hidden="true">
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

@endsection

@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script>
        let dTable = null;

        $(function() {
            dataTable();
        })

        function dataTable() {
            const url = "{{ route('ticket.datatable') }}";
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

        function addData() {
            $("#formEditable").attr('data-action', 'add').fadeIn(200);
            $("#boxTable").removeClass("col-md-12").addClass("col-md-7");
            $("#id").val('');
            $("#type").val('').change();
            $("#status").val('open').change();
            $("#member_id").val('').change();
            $("#technician_id").val('').change();
            $("#cases").val('');
            $("#complaint_image").val('');
        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-7").addClass("col-md-12");
                $("#reset").click();
            })
        }

        $("#formEditable form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            saveData(formData, $("#formEditable").attr("data-action"));
        });

        function saveData(data, action) {
            $.ajax({
                url: action == "update" ? "{{ route('ticket.update') }}" : "{{ route('ticket.create') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                success: function(res) {
                    closeForm();
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                },
                error: function(err) {
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

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

        function removeData(id) {
            let c = confirm("Apakah anda yakin untuk menghapus data ini ?");
            if (c) {
                $.ajax({
                    url: "{{ route('ticket.destroy') }}",
                    method: "DELETE",
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
    </script>
@endpush
