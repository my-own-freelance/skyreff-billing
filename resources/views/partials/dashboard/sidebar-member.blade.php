<!-- Sidebar -->
@php
    $routename = request()->route()->getName();
    $user = Auth()->user();
@endphp
<div class="sidebar sidebar-style-2" data-background-color="{{ $sidebarColor }}">
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-primary">
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">NAVIGATION</h4>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'dashboard' ? 'active' : '' }}">
                    <a href="{{ route('dashboard.teknisi') }}" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item ml-3">
                    <a href="{{ route('user.account') }}">
                        <i class="fas fa-user-cog"></i>
                        <p>Setting Account</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'subscription' ? 'active' : '' }}">
                    <a href="{{ route('subscription') }}">
                        <i class="fas fa-sync-alt"></i> {{-- Subscription --}}
                        <p>Subscription</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'invoice' ? 'active' : '' }}">
                    <a href="{{ route('invoice') }}">
                        <i class="fas fa-file-invoice-dollar"></i> {{-- Invoice --}}
                        <p>Invoice</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'ticket' ? 'active' : '' }}">
                    <a href="{{ route('ticket') }}">
                        <i class="fas fa-ticket-alt"></i> {{-- Ticket --}}
                        <p>Ticket Keluhan</p>
                    </a>
                </li>
                <li class="nav-item ml-3">
                    <a href="{{ route('logout') }}">
                        <i class="fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- End Sidebar -->
