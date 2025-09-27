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
                        <h5 class="text-uppercase title">DATA MEMBER</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                        <button class="btn btn-mini btn-primary" onclick="return addData();">Tambah</button>
                    </div>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="memberDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Nama</th>
                                    <th class="all">Username</th>
                                    <th class="all">Phone</th>
                                    <th class="all">Area</th>
                                    <th class="all">Alamat</th>
                                    <th class="all">Link Maps</th>
                                    <th class="all">Last Login</th>
                                    <th class="all">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="9" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM TAMBAH/EDIT --}}
        <div class="col-md-4 col-sm-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>Tambah / Edit Member</h5>
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
                            <label for="name">Nama</label>
                            <input class="form-control" id="name" type="text" name="name"
                                placeholder="masukkan nama member" required />
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input class="form-control" id="username" type="text" name="username"
                                placeholder="masukkan username member" required />
                        </div>
                        <div class="form-group">
                            <label for="phone">Nomor Telpon</label>
                            <input class="form-control" id="phone" type="text" name="phone"
                                placeholder="masukkan nomor telpon member" required />
                        </div>
                        <div class="form-group">
                            <label for="area_id">Area</label>
                            <select class="form-control" id="area_id" name="area_id" required>
                                <option value="">Pilih Area</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }} - [{{ $area->code }}]</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="address">Alamat</label>
                            <textarea class="form-control" id="address" name="address" placeholder="masukkan alamat member"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="link_maps">Link Maps</label>
                            <input class="form-control" id="link_maps" type="url" name="link_maps"
                                placeholder="masukkan link maps" />
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input class="form-control" id="password" type="password" name="password"
                                placeholder="masukkan password member" />
                            <small class="text-warning">Min 5 Karakter</small>
                        </div>
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-control" id="is_active" name="is_active" required>
                                <option value="">Pilih Status</option>
                                <option value="Y">Aktif</option>
                                <option value="N">Disable</option>
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
        })

        function dataTable() {
            const url = "{{ route('member.datatable') }}";
            dTable = $("#memberDataTable").DataTable({
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
                        data: "name"
                    },
                    {
                        data: "username"
                    },
                    {
                        data: "phone"
                    },
                    {
                        data: "area.name",
                        defaultContent: "-"
                    },
                    {
                        data: "address",
                        defaultContent: "-"
                    },
                    {
                        data: "link_maps",
                        render: function(data) {
                            return data ? `<a href="${data}" class="text-primary" target="_blank">Lihat Maps</a>` : "-";
                        }
                    },
                    {
                        data: "last_login_at_formatted",
                        defaultContent: "-"
                    },
                    {
                        data: "is_active"
                    }
                ],
                pageLength: 25,
            });
        }

        function refreshData() {
            dTable.ajax.reload(null, false);
        }

        function addData() {
            $("#username").removeAttr("readonly");
            $("#formEditable").attr('data-action', 'add').fadeIn(200);
            $("#boxTable").removeClass("col-md-12").addClass("col-md-8");
            $("#name").focus();
        }

        function closeForm() {
            $("#username").removeAttr("readonly");
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-8").addClass("col-md-12");
                $("#reset").click();
            })
        }

        function getData(id) {
            $.ajax({
                url: "{{ route('member.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#boxTable").removeClass("col-md-12").addClass("col-md-8");
                        let d = res.data;
                        $("#id").val(d.id);
                        $("#name").val(d.name);
                        $("#username").val(d.username).attr("readonly", true);
                        $("#phone").val(d.phone);
                        $("#area_id").val(d.area_id).change();
                        $("#address").val(d.address);
                        $("#link_maps").val(d.link_maps);
                        $("#password").val("");
                        $("#is_active").val(d.is_active).change();
                    })
                },
                error: function(err) {
                    showMessage("warning", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        $("#formEditable form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append("id", parseInt($("#id").val()));
            formData.append("name", $("#name").val());
            formData.append("username", $("#username").val());
            formData.append("phone", $("#phone").val());
            formData.append("area_id", $("#area_id").val());
            formData.append("address", $("#address").val());
            formData.append("link_maps", $("#link_maps").val());
            formData.append("password", $("#password").val());
            formData.append("is_active", $("#is_active").val());

            saveData(formData, $("#formEditable").attr("data-action"));
            return false;
        });

        function updateStatus(id, status) {
            let c = confirm(`Anda yakin ingin mengubah status ke ${status} ?`)
            if (c) {
                let dataToSend = new FormData();
                dataToSend.append("is_active", status == "Disabled" ? "N" : "Y");
                dataToSend.append("id", id);
                updateStatusData(dataToSend);
            }
        }

        function saveData(data, action) {
            $.ajax({
                url: action == "update" ? "{{ route('member.update') }}" : "{{ route('member.create') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    $("#submit").attr("disabled", true)
                },
                success: function(res) {
                    $("#submit").attr("disabled", false)
                    closeForm();
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                },
                error: function(err) {
                    $("#submit").attr("disabled", false)
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function removeData(id) {
            let c = confirm("Apakah anda yakin untuk menghapus data ini ?");
            if (c) {
                $.ajax({
                    url: "{{ route('member.destroy') }}",
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

        function updateStatusData(data) {
            $.ajax({
                url: "{{ route('member.change-status') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                success: function(res) {
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                },
                error: function(err) {
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }
    </script>
@endpush
