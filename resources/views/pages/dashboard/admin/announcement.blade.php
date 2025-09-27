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
                        <h5 class="text-uppercase title">List Announcement</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                        <button class="btn btn-mini btn-primary" onclick="return addData();">Tambah Data</button>
                    </div>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="announcementDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Tipe</th>
                                    <th class="all">Status</th>
                                    <th class="all">Subject</th>
                                    <th class="all">Target</th>
                                    <th class="">Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- Form add/update --}}
        <div class="col-md-5 col-sm-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>Tambah / Edit Data</h5>
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
                            <label for="subject">Subject</label>
                            <input class="form-control" id="subject" type="text" name="subject"
                                placeholder="masukkan subject informasi" required />
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <input class="form-control" id="message" type="text" name="message"
                                placeholder="masukkan message" />
                        </div>

                        <div class="form-group">
                            <label for="type">Tipe</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">Pilih Tipe</option>
                                <option value="P">Primary</option>
                                <option value="I">Info</option>
                                <option value="S">Success</option>
                                <option value="W">Warning</option>
                                <option value="D">Danger</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-control" id="is_active" name="is_active" required>
                                <option value="">Pilih Status</option>
                                <option value="Y">Publish</option>
                                <option value="N">Draft</option>
                            </select>
                        </div>

                        {{-- Tambahan Target --}}
                        <div class="form-group">
                            <label for="target_type">Target</label>
                            <select class="form-control" id="target_type" name="target_type">
                                <option value="">Semua</option>
                                <option value="user">User Tertentu</option>
                                <option value="area">Area Tertentu</option>
                            </select>
                        </div>

                        <div class="form-group" id="target_user_box" style="display:none;">
                            <label for="user_id">Pilih Member</label>
                            <select class="form-control" id="user_id" name="user_id">
                                <option value="">-- pilih member --</option>
                                @foreach ($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" id="target_area_box" style="display:none;">
                            <label for="area_id">Pilih Area</label>
                            <select class="form-control" id="area_id" name="area_id">
                                <option value="">-- pilih area --</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a->id }}">{{ $a->name }} - [{{ $a->code }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-sm btn-primary" type="submit" id="submit">
                                <i class="ti-save"></i><span>Simpan</span>
                            </button>
                            <button class="btn btn-sm btn-default" id="reset" type="reset"
                                style="margin-left : 10px;"><span>Reset</span>
                            </button>
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

            // toggle box user/area sesuai target
            $("#target_type").on("change", function() {
                let val = $(this).val();
                $("#target_user_box, #target_area_box").hide();
                if (val === "user") $("#target_user_box").show();
                if (val === "area") $("#target_area_box").show();
            })
        })

        function dataTable() {
            const url = "{{ route('announcement.datatable') }}";
            dTable = $("#announcementDataTable").DataTable({
                searching: true,
                ordering: true,
                lengthChange: true,
                responsive: true,
                processing: true,
                serverSide: true,
                paging: true,
                ajax: url,
                columns: [{
                        data: "action"
                    },
                    {
                        data: "type"
                    },
                    {
                        data: "is_active"
                    },
                    {
                        data: "subject"
                    },
                    {
                        data: "target"
                    }, // tambahan kolom target
                    {
                        data: "message",
                        render: function(data, type) {
                            if (type === 'display') {
                                return `<div class="wrap-text">${data}</div>`;
                            }
                            return data;
                        }
                    }
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
            $("#subject").focus();
        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-7").addClass("col-md-12");
            })
        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-7").addClass("col-md-12");
                $("#reset").click();

                // Reset hidden input id
                $("#id").val("");

                // Reset target_type agar semua box hidden
                $("#target_type").val("").trigger("change");
            });
        }

        function getData(id) {
            $.ajax({
                url: "{{ route('announcement.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    let d = res.data;
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#boxTable").removeClass("col-md-12").addClass("col-md-7");
                        $("#id").val(d.id);
                        $("#subject").val(d.subject);
                        $("#message").val(d.message);
                        $("#type").val(d.type);
                        $("#is_active").val(d.is_active);

                        if (d.user_id) {
                            $("#target_type").val("user").trigger("change");
                            $("#user_id").val(d.user_id);
                        } else if (d.area_id) {
                            $("#target_type").val("area").trigger("change");
                            $("#area_id").val(d.area_id);
                        } else {
                            $("#target_type").val("").trigger("change");
                        }
                    })
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("warning", "flaticon-error", "Peringatan", err.responseJSON?.message);
                }
            })
        }

        $("#formEditable form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append("id", parseInt($("#id").val()));
            formData.append("subject", $("#subject").val());
            formData.append("message", $("#message").val());
            formData.append("type", $("#type").val());
            formData.append("is_active", $("#is_active").val());

            let targetType = $("#target_type").val();
            if (targetType === "user") {
                formData.append("user_id", $("#user_id").val());
                formData.append("area_id", "");
            } else if (targetType === "area") {
                formData.append("area_id", $("#area_id").val());
                formData.append("user_id", "");
            } else {
                formData.append("user_id", "");
                formData.append("area_id", "");
            }

            saveData(formData, $("#formEditable").attr("data-action"));
            return false;
        });

        function updateStatus(id, status) {
            let c = confirm(`Anda yakin ingin mengubah status ke ${status} ?`)
            if (c) {
                let dataToSend = new FormData();
                dataToSend.append("is_active", status == "Draft" ? "N" : "Y");
                dataToSend.append("id", id);
                updateStatusData(dataToSend);
            }
        }

        function saveData(data, action) {
            $.ajax({
                url: action == "update" ? "{{ route('announcement.update') }}" :
                    "{{ route('announcement.create') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    closeForm();
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function removeData(id) {
            let c = confirm("Apakah anda yakin untuk menghapus data ini ?");
            if (c) {
                $.ajax({
                    url: "{{ route('announcement.destroy') }}",
                    method: "DELETE",
                    data: {
                        id: id
                    },
                    beforeSend: function() {
                        console.log("Loading...")
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

        function updateStatusData(data) {
            $.ajax({
                url: "{{ route('announcement.change-status') }}",
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
