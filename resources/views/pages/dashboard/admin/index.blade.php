@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <style>

    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-4 col-lg-4">
            <div class="card">
                <div class="card-body p-3 text-center">
                    <h2 class="mt-2"><b class="text2_primary" style="font-weight: 900;">ESTIMASI TAHUNAN</b></h2>
                    <h1 class="text-primary"><i class="fas fa-money-bill-wave" style="font-size: 300%;"></i></h1>
                    <h4><b style="font-size:150%;" id="w3_balance">{{ $data['trx_total_amount'] }}</b></h4>
                    <div class="text-muted mb-3">Pendapatan Bruto</div>
                    <a class="btn btn-primary text-white btn-block" href="{{ route('invoice') }}"> Invoice Plan</a>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-sm-12">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-body skew-shadow">
                            <h1 class="mt-4">{{ $data['trx_total_amount_bln'] }}</h1>
                            <h3 class="mt-3">Total Bulanan</h3>
                            <div class="pull-right mt-4"><small>Estimasi Bulan Ini</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-body bubble-shadow">
                            <h1 class="mt-4">{{ $data['trx_total_amount_smt1'] }}</h1>
                            <h3 class="mt-3">Semester 1</h3>
                            <div class="pull-right mt-4"><small>Estimasi Semester 1</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-body skew-shadow">
                            <h1 class="mt-4">{{ $data['trx_total_amount_smt2'] }}</h1>
                            <h3 class="mt-3">Semester 2</h3>
                            <div class="pull-right mt-4"><small>Estimasi Semester 2</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3">
                <a href="{{ route('member') }}" style="text-decoration: none; color: inherit;">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md background_primary mr-3"><i class="fas fa-users"></i></span>
                        <div>
                            <h5 class="mb-1"><b>{{ $data['total_member'] }}</b></h5>
                            <small class="text-muted">Total Member</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3">
                <a href="{{ route('invoice') }}" style="text-decoration: none; color: inherit;">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-success mr-3"><i class="fas fa-file-invoice-dollar"></i></span>
                        <div>
                            <h5 class="mb-1"><b>{{ $data['total_invoice_bln'] }}</b></h5>
                            <small class="text-muted">Total Invoice</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3">
                <a href="{{ route('plan') }}" style="text-decoration: none; color: inherit;">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-info mr-3"><i class="fas fa-clipboard-check"></i></span>
                        <div>
                            <h5 class="mb-1"><b>{{ $data['total_plan'] }}</b></h5>
                            <small class="text-muted">Total Plan</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card p-3">
                <a href="{{ route('subscription') }}" style="text-decoration: none; color: inherit;">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-danger mr-3"><i class="fas fa-layer-group"></i></span>
                        <div>
                            <h5 class="mb-1"><b>{{ $data['total_subscription'] }}</b></h5>
                            <small class="text-muted">Total Subscription</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row card-tools-still-right">
                        <h4 class="title">
                            <b>
                                <i class="fas fa-chart-line mr-2"></i>
                                Statik Omset Bulanan
                            </b>
                        </h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 375px">
                        <canvas id="statisticsChart"></canvas>
                    </div>
                    <div id="myChartLegend"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/chart.js/chart.min.js') }}"></script>
    <script>
        $(function() {
            getStatisticChart()
        })

        function getStatisticChart() {
            $.ajax({
                url: "{{ route('statistic-chart') }}",
                method: "GET",
                dataType: "json",
                success: function(res) {
                    console.log("res :", res);
                    renderChart(res.data)
                },
                error: function(err) {
                    console.log("error :", err);
                    renderChart(err.data)
                }
            })
        }

        function renderChart(data) {
            console.log("data :", data)
            //Chart
            var ctx = document.getElementById('statisticsChart').getContext('2d');

            var statisticsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: data
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        bodySpacing: 4,
                        mode: "nearest",
                        intersect: 0,
                        position: "nearest",
                        xPadding: 10,
                        yPadding: 10,
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                                const value = convertToRupiah(tooltipItem.yLabel)

                                return datasetLabel + ': ' + value;
                            }
                        }
                    },
                    layout: {
                        padding: {
                            left: 5,
                            right: 5,
                            top: 15,
                            bottom: 15
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                fontStyle: "500",
                                beginAtZero: false,
                                maxTicksLimit: 5,
                                padding: 10,
                                callback: function(value) {
                                    return convertToRupiah(value)
                                }
                            },
                            gridLines: {
                                drawTicks: false,
                                display: false
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                zeroLineColor: "transparent"
                            },
                            ticks: {
                                padding: 10,
                                fontStyle: "500"
                            }
                        }]
                    },
                    legendCallback: function(chart) {
                        var text = [];
                        text.push('<ul class="' + chart.id + '-legend html-legend">');
                        for (var i = 0; i < chart.data.datasets.length; i++) {
                            text.push('<li><span style="background-color:' + chart.data.datasets[i]
                                .legendColor +
                                '"></span>');
                            if (chart.data.datasets[i].label) {
                                text.push(chart.data.datasets[i].label);
                            }
                            text.push('</li>');
                        }
                        text.push('</ul>');
                        return text.join('');
                    }
                }
            });

            var myLegendContainer = document.getElementById("myChartLegend");

            // generate HTML legend
            myLegendContainer.innerHTML = statisticsChart.generateLegend();

            // bind onClick event to all LI-tags of the legend
            var legendItems = myLegendContainer.getElementsByTagName('li');
            for (var i = 0; i < legendItems.length; i += 1) {
                legendItems[i].addEventListener("click", legendClickCallback, false);
            }
        }
    </script>
@endpush
