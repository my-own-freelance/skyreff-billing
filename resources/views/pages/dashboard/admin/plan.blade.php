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
                        <h5 class="text-uppercase title">List Plan</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                        <button class="btn btn-mini btn-primary" onclick="return addData();">Tambah Data</button>
                    </div>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="planDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Gambar</th>
                                    <th class="all">Nama</th>
                                    <th class="all">Harga</th>
                                    <th class="all">Level</th>
                                    <th class="all">Status</th>
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

        {{-- Form Tambah/Edit --}}
        <div class="col-md-5 col-sm-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>Tambah / Edit Plan</h5>
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
                            <label for="name">Nama Plan</label>
                            <input class="form-control" id="name" type="text" name="name"
                                placeholder="Masukkan nama plan" required />
                        </div>
                        <div class="form-group">
                            <label for="price">Harga</label>
                            <input class="form-control" id="price" type="number" name="price"
                                placeholder="Masukkan harga (Rp)" required />
                        </div>
                        <div class="form-group">
                            <label for="level">Level</label>
                            <select class="form-control" id="level" name="level" required>
                                <option value="">Pilih Level</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">Level {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Masukkan deskripsi plan"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-control" id="is_active" name="is_active" required>
                                <option value="">Pilih Status</option>
                                <option value="Y">Aktif</option>
                                <option value="N">Nonaktif</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="image">Gambar</label>
                            <input class="form-control" id="image" type="file" name="image" />
                            <small class="text-danger">Max ukuran 2MB</small>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-sm btn-primary" type="submit" id="submit">
                                <i class="ti-save"></i><span>Simpan</span>
                            </button>
                            <button class="btn btn-sm btn-default" id="reset" type="reset"
                                style="margin-left : 10px;">
                                <span>Reset</span>
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
            const url = "{{ route('plan.datatable') }}";
            dTable = $("#planDataTable").DataTable({
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
                        data: "image"
                    },
                    {
                        data: "name"
                    },
                    {
                        data: "price"
                    },
                    {
                        data: "level"
                    },
                    {
                        data: "is_active"
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
            $("#image").attr("required", true);
            $("#name").focus();
        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-7").addClass("col-md-12");
                $("#reset").click();
            })
        }

        function getData(id) {
            $.ajax({
                url: "{{ route('plan.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#boxTable").removeClass("col-md-12").addClass("col-md-7");
                        let d = res.data;
                        $("#id").val(d.id);
                        $("#name").val(d.name);
                        $("#price").val(d.price);
                        $("#level").val(d.level).change();
                        $("#description").val(d.description);
                        $("#is_active").val(d.is_active).change();
                        $("#image").attr("required", false);
                    })
                },
                error: function(err) {
                    console.log("error :", err);
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
            formData.append("price", $("#price").val());
            formData.append("level", $("#level").val());
            formData.append("description", $("#description").val());
            formData.append("is_active", $("#is_active").val());
            formData.append("image", document.getElementById("image").files[0]);

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
                url: action == "update" ? "{{ route('plan.update') }}" : "{{ route('plan.create') }}",
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
                    url: "{{ route('plan.destroy') }}",
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
                url: "{{ route('plan.change-status') }}",
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
