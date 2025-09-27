@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <style>
        .avatar {
            background-color: #f8f9fa;
            /* Ubah ini sesuai kebutuhan untuk kontras yang baik */
            padding: 10px;
            border-radius: 50%;
        }

        .avatar-img {
            display: block;
            max-width: 100%;
            height: auto;
        }

        .logo-container {
            text-align: center;
        }

        .logo-container h1 {
            margin-top: 10px;
            font-size: 1.5rem;
            /* Atur ukuran font jika diperlukan */
            color: #333;
            /* Warna teks agar kontras dengan latar belakang */
        }
    </style>
@endpush
@section('content')
    <div class="row mb-5">
        <div class="col-md-12" id="boxTable">
            <div class="card card-with-nav">
                <div class="card-header">
                    <div class="card-header-left my-3">
                        <h5 class="text-uppercase title">Management Application</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form id="formCountInformation">
                        <input type="hidden" name="id" id="id">
                        <div class="tab-pane active" id="countinformation" (role="tabpanel")>
                            <div class="row mt-3 mb-5">
                                <div class="col-md-3">
                                    <div class=" mb-3" id="metaImage">
                                        <img src="{{ asset('frontpage/images/mockup-depan.jpg') }}"
                                            style="object-fit: cover; width: 65%" alt="Main Meta Image" class="">
                                    </div>
                                    <h1>Main Meta Image</h1>
                                </div>
                                <div class="col-md-3 logo-container">
                                    <div class="avatar avatar-xxl mb-3" id="imageLogo">
                                        <img src="{{ asset('frontpage/images/logo-purple.svg') }}" alt="Logo Full Color"
                                            class="avatar-img rounded-circle">
                                    </div>
                                    <h1>Logo Full Color</h1>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group form-group-default">
                                        <label>Judul Web/Aplikasi</label>
                                        <input type="text" class="form-control" id="web_title" name="web_title"
                                            placeholder="Judul Website">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default">
                                        <label>Nomor Telpon</label>
                                        <input type="text" class="form-control" id="phone_number" name="phone_number"
                                            placeholder="Nomor Telpon">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default">
                                        <label>Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="Email">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group form-group-default">
                                        <label>Facebook</label>
                                        <input type="text" class="form-control" id="facebook" name="facebook"
                                            placeholder="Link Facebook">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default">
                                        <label>Twitter</label>
                                        <input type="text" class="form-control" id="twitter" name="twitter"
                                            placeholder="Link Twitter">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default">
                                        <label>Instagram</label>
                                        <input type="text" class="form-control" id="instagram" name="instagram"
                                            placeholder="Link Instagram">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group form-group-default">
                                                <label>Youtube</label>
                                                <input type="text" class="form-control" id="youtube" name="youtube"
                                                    placeholder="Link Youtube">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group form-group-default">
                                                <label>Alamat</label>
                                                <input type="text" class="form-control" id="address" name="address"
                                                    placeholder="Alamat">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group form-group-default">
                                                <label for="location">Link Embed Google Maps (hanya masukan link https
                                                    saja)</label>
                                                <textarea class="form-control" id="maps_location" name="maps_location style="width:100%""
                                                    placeholder="https://www.google.com/maps/embed?" rows="5"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group form-group-default" id="maps_preview">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group form-group-default">
                                                <label>Main Meta Image</label>
                                                <input class="form-control" id="meta_image" type="file"
                                                    name="meta_image" placeholder="upload meta image" />
                                                <small class="text-danger">Max ukuran 1MB</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group form-group-default">
                                                <label>Logo (Full Color)</label>
                                                <input class="form-control" id="web_logo" type="file"
                                                    name="web_logo" placeholder="upload logo berwarna" />
                                                <small class="text-danger">Max ukuran 1MB</small>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group form-group-default">
                                                <label>Deskripsi Web/Aplikasi</label>
                                                <textarea class="form-control" name="web_description" id="web_description" rows="5" style="width:100%"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
    <script>
        $(function() {
            getData()
        })

        $("#formCountInformation").submit(function(e) {
            e.preventDefault()

            let formData = new FormData();
            formData.append("id", parseInt($("#id").val()));
            formData.append("web_title", $("#web_title").val());
            formData.append("phone_number", $("#phone_number").val());
            formData.append("email", $("#email").val());
            formData.append("facebook", $("#facebook").val());
            formData.append("twitter", $("#twitter").val());
            formData.append("instagram", $("#instagram").val());
            formData.append("youtube", $("#youtube").val());
            formData.append("address", $("#address").val());
            formData.append("web_description", $("#web_description").val());
            formData.append("maps_location", $("#maps_location").val());
            formData.append("web_logo", document.getElementById("web_logo").files[0]);
            formData.append("meta_image", document.getElementById("meta_image").files[0]);

            createAndUpdate(formData);
            return false;
        });

        function getData() {
            $.ajax({
                url: "{{ route('web-config.detail') }}",
                dataType: "json",
                success: function(data) {
                    let d = data.data;
                    $("#id").val(d.id);
                    $("#web_title").val(d.web_title);
                    $("#phone_number").val(d.phone_number);
                    $("#email").val(d.email);
                    $("#facebook").val(d.facebook);
                    $("#twitter").val(d.twitter);
                    $("#instagram").val(d.instagram);
                    $("#youtube").val(d.youtube);
                    $("#address").val(d.address);
                    $("#web_description").val(d.web_description);
                    $("#maps_location").val(d.maps_location);
                    if (d.maps_location && d.maps_preview) {
                        $("#maps_preview").empty().append(d.maps_preview);
                    }

                    if (d.web_logo) {
                        $('#imageLogo img').attr('src', d.web_logo);
                    }
                    if (d.meta_image) {
                        $('#metaImage img').attr('src', d.meta_image);
                    }

                },
                error: function(err) {
                    console.log("error :", err)
                }

            })
        }

        function createAndUpdate(data) {
            $.ajax({
                url: "{{ route('web-config.update') }}",
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
                    }, 800);
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
