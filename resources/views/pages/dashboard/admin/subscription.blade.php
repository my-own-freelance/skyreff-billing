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
                        <button class="btn btn-mini btn-primary" onclick="return addData();">Tambah</button>
                    </div>
                    <form class="navbar-left navbar-form mr-md-1 mt-3" id="formFilter">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fSortBy">Sorting By</label>
                                    <select class="form-control" id="fSortBy" name="fSortBy">
                                        <option value="created_at">Date Created</option>
                                        <option value="subscription_number">Number</option>
                                        <option value="type">Type</option>
                                        <option value="status">Status</option>
                                        <option value="next_invoice_at">Next Invoice</option>
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
                                <div class="form-group">
                                    <label for="fFilterMember">Filter Member</label>
                                    <select class="form-control" id="fFilterMember" name="fFilterMember">
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
                                <div class="form-group">
                                    <label for="fFilterPlan">Filter Plan</label>
                                    <select class="form-control" id="fFilterPlan" name="fFilterPlan">
                                        <option value="">All</option>
                                        @foreach ($plans as $plan)
                                            <option value="{{ $plan->id }}">
                                                {{ $plan->name }}
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
                        <table class="table table-striped table-bordered nowrap dataTable" id="subscriptionDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
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
                                    <td colspan="9" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>Tambah / Edit Subscription</h5>
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

                        <!-- Member -->
                        <div class="form-group">
                            <label for="user_id">Member</label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                <option value="">Pilih Member</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->username }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Plan -->
                        <div class="form-group">
                            <label for="plan_id">Plan</label>
                            <select class="form-control" id="plan_id" name="plan_id" required>
                                <option value="">Pilih Plan</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} - Rp
                                        {{ number_format($plan->price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tipe -->
                        <div class="form-group">
                            <label for="type">Tipe</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">Pilih Tipe</option>
                                <option value="pppoe">PPPoE</option>
                                <option value="hotspot">Hotspot</option>
                                <option value="static">Static</option>
                            </select>
                        </div>

                        <!-- PPPoE/Hotspot Username -->
                        <div class="form-group" id="usernameGroup">
                            <label for="username">PPPoE/Hotspot Username</label>
                            <input class="form-control" id="username" type="text" name="username"
                                placeholder="opsional" />
                        </div>

                        <!-- PPPoE/Hotspot Password -->
                        <div class="form-group" id="passwordGroup">
                            <label for="password">PPPoE/Hotspot Password</label>
                            <input class="form-control" id="password" type="text" name="password"
                                placeholder="opsional" />
                        </div>

                        <!-- Queue (untuk Static) -->
                        <div class="form-group" id="queueGroup" style="display:none;">
                            <label for="queue">Queue</label>
                            <input class="form-control" id="queue" type="text" name="queue"
                                placeholder="opsional" />
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="active">Active</option>
                                <option value="isolir">Isolir</option>
                            </select>
                        </div>

                        <!-- Periode -->
                        <div class="form-group">
                            <label for="current_period_start">Periode Mulai</label>
                            <input class="form-control" id="current_period_start" type="datetime-local"
                                name="current_period_start" />
                        </div>

                        <div class="form-group">
                            <label for="current_period_end">Periode Berakhir</label>
                            <input class="form-control" id="current_period_end" type="datetime-local"
                                name="current_period_end" />
                        </div>

                        <!-- Next Invoice -->
                        <div class="form-group">
                            <label for="next_invoice_at">Next Invoice</label>
                            <input class="form-control" id="next_invoice_at" type="datetime-local"
                                name="next_invoice_at" />
                            <small class="text-muted">Tanggal ini akan digunakan untuk generate invoice otomatis</small>
                        </div>

                        <!-- Submit -->
                        <div class="form-group mt-3">
                            <button class="btn btn-sm btn-primary" type="submit" id="submit">
                                <i class="ti-save"></i><span>Simpan</span>
                            </button>
                            <button class="btn btn-sm btn-default" id="reset" type="reset"
                                style="margin-left:10px;">
                                <span>Reset</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Manage Device -->
    <div class="modal fade" id="manageDeviceModal" tabindex="-1" role="dialog"
        aria-labelledby="manageDeviceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Devices for Subscription <span id="subscriptionNumber"></span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom: 1rem;">
                        <label for="addDeviceSelect" class="form-label fw-semibold">Add Device</label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <!-- Select Input -->
                            <select id="addDeviceSelect" style="flex: 1; padding: 0.375rem 0.75rem;">
                                <option value="">-- Select Device --</option>
                                @foreach ($devices as $device)
                                    <option value="{{ $device->id }}">
                                        {{ $device->name }}@if ($device->ip_address)
                                            - {{ $device->ip_address }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            <!-- Add Button -->
                            <button style="padding: 0.375rem 1rem;" class="btn btn-primary" id="addDeviceBtn">
                                Add Device
                            </button>
                        </div>
                    </div>

                    <hr>
                    <h6>Devices Used in Subscription</h6>
                    <ul id="subscriptionDeviceList" class="list-group"></ul>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            function toggleInputByType() {
                const type = $('#type').val();
                if (type === 'static') {
                    $('#usernameGroup, #passwordGroup').hide();
                    $('#queueGroup').show();
                } else if (type === 'pppoe' || type === 'hotspot') {
                    $('#usernameGroup, #passwordGroup').show();
                    $('#queueGroup').hide();
                } else {
                    // default
                    $('#usernameGroup, #passwordGroup').show();
                    $('#queueGroup').hide();
                }
            }

            // initial toggle on page load
            toggleInputByType();

            // toggle on type change
            $('#type').on('change', function() {
                toggleInputByType();
            });
        });

        let dTable = null;

        $(function() {
            dataTable();
        });

        function dataTable(filter) {
            let url = "{{ route('subscription.datatable') }}";
            if (filter) url += "?" + filter;

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
                columns: [{
                        data: "action"
                    },
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

        $('#formFilter').submit(function(e) {
            e.preventDefault()
            let dataFilter = {
                sort_by: $("#fSortBy").val(),
                sort_type: $("#fSortType").val(),
                user_id: $("#fFilterMember").val(),
                plan_id: $("#fFilterPlan").val()
            }

            dTable.clear();
            dTable.destroy();
            dataTable($.param(dataFilter))
            return false
        })



        function addData() {
            $("#formEditable").attr('data-action', 'add').fadeIn(200);
            $("#boxTable").removeClass("col-md-12").addClass("col-md-8");
            $("#user_id").focus();
        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-8").addClass("col-md-12");
                $("#reset").click();
            })
        }

        function getData(id) {
            $.ajax({
                url: "{{ route('subscription.detail', ['id' => ':id']) }}".replace(':id', id),
                method: "GET",
                dataType: "json",
                success: function(res) {
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#boxTable").removeClass("col-md-12").addClass("col-md-8");
                        let d = res.data;

                        // isi form
                        $("#id").val(d.id);
                        $("#user_id").val(d.user_id).change();
                        $("#plan_id").val(d.plan_id).change();
                        $("#type").val(d.type).change();
                        $("#username").val(d.username);
                        $("#password").val(d.password);
                        $("#queue").val(d.queue);
                        $("#status").val(d.status).change();

                        // format datetime-local (contoh: 2025-10-01T13:30)
                        function formatDateTimeLocal(dt) {
                            if (!dt) return "";
                            let date = new Date(dt);
                            let year = date.getFullYear();
                            let month = String(date.getMonth() + 1).padStart(2, "0");
                            let day = String(date.getDate()).padStart(2, "0");
                            let hours = String(date.getHours()).padStart(2, "0");
                            let minutes = String(date.getMinutes()).padStart(2, "0");
                            return `${year}-${month}-${day}T${hours}:${minutes}`;
                        }

                        $("#current_period_start").val(formatDateTimeLocal(d.current_period_start));
                        $("#current_period_end").val(formatDateTimeLocal(d.current_period_end));
                        $("#next_invoice_at").val(formatDateTimeLocal(d.next_invoice_at));
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

            let action = $("#formEditable").attr("data-action"); // add / update

            let payload = {
                id: parseInt($("#id").val()) || null,
                user_id: $("#user_id").val(),
                plan_id: $("#plan_id").val(),
                type: $("#type").val(),
                username: $("#username").val(),
                password: $("#password").val(),
                queue: $("#queue").val(),
                status: $("#status").val(),
                current_period_start: $("#current_period_start").val(),
                current_period_end: $("#current_period_end").val(),
                next_invoice_at: $("#next_invoice_at").val(),
            };

            saveData(action, payload);
            return false;
        });

        function saveData(action, payload) {
            let url = action === "update" ?
                "{{ route('subscription.update') }}" :
                "{{ route('subscription.create') }}";

            $.ajax({
                url: url,
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify(payload),
                beforeSend: function() {
                    $("#submit").attr("disabled", true);
                    console.log("Sending data...");
                },
                success: function(res) {
                    $("#submit").attr("disabled", false);
                    closeForm();
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                },
                error: function(err) {
                    $("#submit").attr("disabled", false);
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            });
        }

        function updateStatus(id, status) {
            let c = confirm(`Anda yakin ingin mengubah status ke ${status} ?`)
            if (c) {
                let dataToSend = new FormData();
                dataToSend.append("status", status);
                dataToSend.append("id", id);
                updateStatusData(dataToSend);
            }
        }

        function updateStatusData(data) {
            console.log("payload :", data)
            $.ajax({
                url: "{{ route('subscription.change-status') }}",
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

        function generateInvoice(subscriptionId) {
            if (!subscriptionId) {
                showMessage("danger", "flaticon-error", "Peringatan", "Subscription ID tidak valid");
                return false;
            }

            if (!confirm("Apakah Anda yakin ingin membuat invoice untuk subscription ini?")) {
                return false;
            }

            $.ajax({
                url: "{{ route('subscription.generate-invoice') }}", // pastikan route ini ada di web.php/api.php
                method: "POST",
                data: {
                    subscription_id: subscriptionId,
                },
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                    setTimeout(() => {
                        window.location.href = "{{ route('invoice') }}";
                    }, 3000)
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            });

            return false;
        }

        // MANAGE DEVICE SUBSCRIPTION
        let currentSubscriptionId = null;

        function manageDevices(subscriptionId) {
            currentSubscriptionId = subscriptionId;
            $('#manageDeviceModal').modal('show');

            // Panggil API devices berdasarkan route name
            $.get(`{{ route('subscription.device', ['id' => ':id']) }}`.replace(':id', subscriptionId), function(res) {
                if (res.status === 'success') {
                    $('#subscriptionNumber').text(res.data.subscription_number);

                    // Populate registered devices list
                    renderRegisteredDevices(res.data.devices);
                } else {
                    alert(res.message);
                }
            });
        }

        // Render list device yang terdaftar
        function renderRegisteredDevices(devices) {
            let list = '';
            devices.forEach(device => {
                list += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    ${device.name}
                    <button class="btn btn-danger btn-sm" onclick="removeDevice(${device.id})">Remove</button>
                </li>`;
            });
            $('#subscriptionDeviceList').html(list);
        }

        // Tambah device
        $('#addDeviceBtn').click(function() {
            let deviceId = $('#addDeviceSelect').val();
            if (!deviceId) return alert('Please select a device');

            $.ajax({
                url: `{{ route('subscription.add-device', ['id' => ':id']) }}`
                    .replace(':id', currentSubscriptionId), // pastikan route ini ada di web.php/api.php
                method: "POST",
                data: {
                    device_id: deviceId
                },
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    manageDevices(currentSubscriptionId); // reload list
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                        .responseJSON
                        ?.message);
                }
            });
        });


        // Hapus device
        function removeDevice(deviceId) {
            if (!confirm('Are you sure?')) return;

            $.ajax({
                url: `{{ route('subscription.remove-device', ['id' => ':id', 'deviceId' => ':deviceId']) }}`
                    .replace(':id', currentSubscriptionId)
                    .replace(':deviceId', deviceId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status === 'success') manageDevices(currentSubscriptionId);
                    else alert(res.message);
                }
            });
        }

        // END MANAAGE DEVICE SUBSCRIPTION
    </script>
@endpush
