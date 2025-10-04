@extends('layouts.dashboard')
@section('title', $title)
@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fab fa-whatsapp"></i> Broadcast WhatsApp</h5>
                    </div>
                    <div class="card-body">
                        <form id="broadcastForm">
                            @csrf

                            <!-- Tujuan -->
                            <div class="form-group">
                                <label for="broadcastType" class="font-weight-bold">Tujuan</label>
                                <select class="form-control" name="type" id="broadcastType" required>
                                    <option value="">-- Pilih Tujuan --</option>
                                    <option value="custom">Custom</option>
                                    <option value="area">Berdasarkan Area</option>
                                    <option value="member">Pilih Member</option>
                                </select>
                            </div>

                            <!-- Custom Input -->
                            <div class="form-group d-none" id="customInput">
                                <label for="custom_numbers">Nomor Tujuan <small class="text-muted">(pisahkan dengan
                                        koma)</small></label>
                                <input type="text" name="custom_numbers" class="form-control"
                                    placeholder="Contoh: 08123456789, 08987654321">
                            </div>

                            <!-- Area Select -->
                            <div class="form-group d-none" id="areaInput">
                                <label for="area_id">Pilih Area</label>
                                <select name="area_id" class="form-control">
                                    <option value="">-- Pilih Area --</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Member Select -->
                            <div class="form-group d-none" id="memberInput">
                                <label for="member_ids">Filter Member</label>
                                <div class="select2-input">
                                    <select class="form-control" id="member_ids" name="member_ids[]"  multiple="multiple">
                                        <option value="all">All</option>
                                        @foreach ($members as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }} - {{ $member->phone }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Pesan -->
                            <div class="form-group">
                                <label for="message" class="font-weight-bold">Pesan Broadcast</label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Tulis pesan di sini..." required></textarea>
                            </div>

                            <!-- Submit -->
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-success" id="sendBroadcast">
                                    <i class="fas fa-paper-plane"></i> Kirim Broadcast
                                </button>
                            </div>
                        </form>

                        <!-- Alert -->
                        <div id="alertBox" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('dashboard/js/plugin/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#member_ids").select2({
                theme: "bootstrap"
            })

            // Show/Hide input sesuai pilihan
            $("#broadcastType").change(function() {
                let type = $(this).val();
                $("#customInput, #areaInput, #memberInput").addClass("d-none");

                if (type === "custom") $("#customInput").removeClass("d-none");
                if (type === "area") $("#areaInput").removeClass("d-none");
                if (type === "member") $("#memberInput").removeClass("d-none");
            });

            // Ajax submit
            $("#broadcastForm").submit(function(e) {
                e.preventDefault();
                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('broadcast.send') }}",
                    method: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#alertBox").html(
                            '<div class="alert alert-info">Mengirim broadcast...</div>');
                        $("#sendBroadcast").attr('disabled', true);
                    },
                    success: function(res) {
                        $("#alertBox").html('<div class="alert alert-success">' + res.message +
                            '</div>');
                        $("#broadcastForm")[0].reset();
                        $("#customInput, #areaInput, #memberInput").addClass("d-none");
                        $("#sendBroadcast").attr('disabled', false);

                    },
                    error: function(err) {
                        let msg = err.responseJSON?.message || "Terjadi kesalahan!";
                        $("#alertBox").html('<div class="alert alert-danger">' + msg +
                            '</div>');
                        $("#sendBroadcast").attr('disabled', false);

                    }
                });
            });
        });
    </script>
@endpush
