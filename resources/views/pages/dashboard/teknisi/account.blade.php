@extends('layouts.dashboard')
@section('title', $title)
@section('content')
    <div class="row mb-5">
        <div class="col-md-6" id="boxTable">
            <div class="card card-with-nav">
                <div class="card-header">
                    <div class="card-header-left my-3">
                        <h5 class="text-uppercase title">Management Account</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form id="formCountInformation">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="avatar avatar-xxl mb-3" id="imageProfile">
                                    <img src="{{ asset('dashboard/img/jm_denis.jpg') }}" alt="..."
                                        class="avatar-img rounded-circle">
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="id" id="id">
                        <div class="tab-pane active" id="countinformation" (role="tabpanel")>
                            <div class="form-group form-group-default">
                                <label>Nama</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="nama">
                            </div>
                            <div class="form-group form-group-default">
                                <label>Username</label>
                                <input type="text" class="form-control" id="username" name="username" disabled
                                    placeholder="username">
                            </div>
                            <div class="form-group form-group-default">
                                <label>Password</label>
                                <input type="text" class="form-control" id="password" name="password"
                                    placeholder="ubah password">
                            </div>
                            <div class="form-group form-group-default">
                                <label>Nomor Telpon</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    placeholder="nomor telpon">
                            </div>
                            <div class="form-group form-group-default">
                                <label for="address">Alamat</label>
                                <input class="form-control" id="address" type="text" name="address"
                                    placeholder="masukan alamat" required />
                            </div>
                            <div class="form-group form-group-default">
                                <label>Nama Bank</label>
                                <input type="text" class="form-control" id="bank_type" name="bank_type"
                                    placeholder="nama Bank">
                            </div>
                            <div class="form-group form-group-default">
                                <label>No Rekening</label>
                                <input type="text" class="form-control" id="bank_account" name="bank_account"
                                    placeholder="nomor rekening">
                            </div>
                        </div>
                        <div class="text-right mt-3 mb-3">
                            <button class="btn btn-success" type="submit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/select2/select2.full.min.js') }}"></script>

    <script>
        $('#gender,#province_id,#district_id,#sub_district_id').select2({
            theme: "bootstrap"
        });

        $(function() {
            getData()
        })

        $("#formCountInformation").submit(function(e) {
            e.preventDefault()

            let formData = new FormData();
            formData.append("id", parseInt($("#id").val()));
            formData.append("name", $("#name").val());
            formData.append("username", $("#username").val());
            formData.append("phone", $("#phone").val());
            formData.append("password", $("#password").val());
            formData.append("address", $("#address").val());
            formData.append("bank_type", $("#bank_type").val());
            formData.append("bank_account", $("#bank_account").val());

            update(formData);
            return false;
        });

        function getData() {
            $.ajax({
                url: "{{ route('user.detail-account') }}",
                dataType: "json",
                success: function(data) {
                    let d = data.data;
                    $("#id").val(d.id);
                    $("#name").val(d.name);
                    $("#username").val(d.username);
                    $("#phone").val(d.phone);
                    $("#bank_type").val(d.bank_type);
                    $("#bank_account").val(d.bank_account);

                    $("#address").val(d.address);

                },
                error: function(err) {
                    console.log("error :", err)
                }

            })
        }

        function update(data) {
            $.ajax({
                url: "{{ route('user.update-account') }}",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    setTimeout(() => {
                        window.location.reload()
                    }, 2000);
                },
                error: function(err) {
                    console.log("error :", err)
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message)
                }
            })
        }
    </script>
@endpush
