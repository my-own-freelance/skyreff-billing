@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <style>
        .faq-question {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .faq-question:hover {
            background-color: #f1f5f9;
            /* abu hover */
            cursor: pointer;
        }

        .faq-icon {
            font-weight: bold;
            transition: transform 0.3s ease;
        }

        .faq-answer {
            border-top: 1px solid #e5e7eb;
            font-size: 0.95rem;
            line-height: 1.6;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">

        {{-- Device Detail --}}
        <div class="card mb-4 shadow-sm">
            <div class="row g-0">
                <div class="col-md-4">
                    @if ($device->image)
                        <img src="{{ asset('storage/' . $device->image) }}" class="img-fluid rounded-start"
                            alt="{{ $device->name }}">
                    @else
                        <img src="https://via.placeholder.com/400x300?text=No+Image" class="img-fluid rounded-start"
                            alt="No Image">
                    @endif
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h3 class="card-title">{{ $device->name }}</h3>
                        <p class="card-text text-muted">{{ $device->excerpt }}</p>
                        <p>
                            <span class="badge bg-{{ $device->is_active == 'Y' ? 'success' : 'secondary' }}">
                                {{ $device->is_active == 'Y' ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                        <p class="card-text">{!! $device->description !!}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Device FAQ --}}
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h4 class="mb-3">Frequently Asked Questions</h4>

                @if ($deviceFaq->isEmpty())
                    <p class="text-muted">Belum ada FAQ untuk device ini.</p>
                @else
                    <div class="faq-list">
                        @foreach ($deviceFaq as $index => $faq)
                            <div class="faq-item mb-2 border rounded">
                                <div class="faq-question d-flex justify-content-between align-items-center px-3 py-3 bg-light cursor-pointer"
                                    data-target="#faq-{{ $index }}">
                                    <span class="fw-semibold text-dark">{{ $faq->question }}</span>
                                    <span class="faq-icon fs-4 text-primary">+</span>
                                </div>
                                <div class="faq-answer px-3 py-3 bg-body-tertiary text-secondary" style="display: none;"
                                    id="faq-{{ $index }}">
                                    {!! nl2br(e($faq->answer)) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>


    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $(".faq-question").click(function() {
                let target = $(this).data("target");
                let answer = $(target);

                // Tutup semua kecuali yang diklik
                $(".faq-answer").not(answer).hide();
                $(".faq-icon").text("+").removeClass("text-danger").addClass("text-primary");

                // Toggle langsung tanpa animasi
                answer.toggle();
                let icon = $(this).find(".faq-icon");
                if (icon.text() === "+") {
                    icon.text("−").removeClass("text-primary").addClass("text-danger");
                } else {
                    icon.text("+").removeClass("text-danger").addClass("text-primary");
                }
            });
        });
    </script>
@endpush
