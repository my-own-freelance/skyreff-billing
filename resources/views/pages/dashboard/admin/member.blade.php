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
                    <form class="navbar-left navbar-form mr-md-1 mt-3" id="formFilter">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fSortBy">Sorting By</label>
                                    <select class="form-control" id="fSortBy" name="fSortBy">
                                        <option value="created_at">Date Created</option>
                                        <option value="name">Name</option>
                                        <option value="username">Username</option>
                                        <option value="phone">Phone</option>
                                        <option value="address">Alamat</option>
                                        <option value="last_login_at">Last Login</option>
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
                                <div class="pt-3">
                                    <button class="mt-4 btn btn-sm btn-success mr-3" type="submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
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
                                    <option value="{{ $area->id }}">{{ $area->name }} - [{{ $area->code }}]
                                    </option>
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
                        <!-- Tiket Teknisi -->
                        <div class="form-group" id="divTask">
                            <label for="fCreateTask">Buat Tiket Teknisi</label>
                            <select id="fCreateTask" name="fCreateTask" class="form-control">
                                <option value="tidak">Tidak</option>
                                <option value="ya">Ya</option>
                            </select>
                        </div>

                        <!-- Pilih Teknisi dan Notifikasi -->
                        <div class="form-group" id="divTechnician" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="fTechnician">Pilih Teknisi</label>
                                    <select id="fTechnician" name="fTechnician" class="form-control select2">
                                        <option value="">Pilih Teknisi</option>
                                        @foreach ($technicians as $tech)
                                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="fTechnicianNotif">Kirim Notif Ke Teknisi</label>
                                    <select id="fTechnicianNotif" name="fTechnicianNotif" class="form-control">
                                        <option value="ya">Ya</option>
                                        <option value="tidak">Tidak</option>
                                    </select>
                                </div>
                            </div>
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

        function dataTable(filter) {
            let url = "{{ route('member.datatable') }}";
            if (filter) url += "?" + filter;

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
                            return data ?
                                `<a href="${data}" class="text-primary" target="_blank">Lihat Maps</a>` :
                                "-";
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

        $('#formFilter').submit(function(e) {
            e.preventDefault()
            let dataFilter = {
                sort_by: $("#fSortBy").val(),
                sort_type: $("#fSortType").val()
            }

            dTable.clear();
            dTable.destroy();
            dataTable($.param(dataFilter))
            return false
        })

        function addData() {
            $("#username").removeAttr("readonly");
            $("#formEditable").attr('data-action', 'add').fadeIn(200);
            $("#boxTable").removeClass("col-md-12").addClass("col-md-8");
            $("#name").focus();
            $('#divTask').slideDown();
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
                        $('#divTechnician').slideUp();
                        $('#divTask').slideUp();

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
            // TIKET TEKNISI
            formData.append("create_task", $("#fCreateTask").val());
            formData.append("technician_id", $("#fTechnician").val());
            formData.append("create_pic_notif", $("#fTechnicianNotif").val());

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

        // TEKNISI
        // Toggle divTechnician berdasarkan fCreateTask
        $('#fCreateTask').on('change', function() {
            if ($(this).val() === 'ya') {
                $('#divTechnician').slideDown();
            } else {
                $('#divTechnician').slideUp();
                $('#fTechnician').val('');
                $('#fTechnicianNotif').val('ya');
            }
        });
    </script>
@endpush
