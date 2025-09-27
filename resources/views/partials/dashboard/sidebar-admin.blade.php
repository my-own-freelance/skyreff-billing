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
                    <a href="{{ route('dashboard.admin') }}" aria-expanded="false">
                        <i class="fas fa-tachometer-alt"></i> {{-- Dashboard --}}
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- MASTER --}}
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">MASTER</h4>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'area' ? 'active' : '' }}">
                    <a href="{{ route('area') }}">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Area</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'owner' ? 'active' : '' }}">
                    <a href="{{ route('owner') }}">
                        <i class="fas fa-user-tie"></i>
                        <p>Owner</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'teknisi' ? 'active' : '' }}">
                    <a href="{{ route('teknisi') }}">
                        <i class="fas fa-user-cog"></i> {{-- Teknisi --}}
                        <p>Teknisi</p>
                    </a>
                </li>
                <li class="nav-item ml-3  {{ $routename == 'member' ? 'active' : '' }}">
                    <a href="{{ route('member') }}">
                        <i class="fas fa-users"></i> {{-- Member --}}
                        <p>Member</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'device' ? 'active' : '' }}">
                    <a href="{{ route('device') }}">
                        <i class="fas fa-server"></i> {{-- Device --}}
                        <p>Device</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'device-faq' ? 'active' : '' }}">
                    <a href="{{ route('faq') }}">
                        <i class="fas fa-question-circle"></i> {{-- Device FAQ --}}
                        <p>Device FAQ</p>
                    </a>
                </li>
                <li class="nav-item ml-3">
                    <a href="#">
                        <i class="fas fa-box-open"></i> {{-- Paket Plan --}}
                        <p>Paket Plan</p>
                    </a>
                </li>

                {{-- MANAGE --}}
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">MANAGE</h4>
                </li>
                <li class="nav-item ml-3">
                    <a href="#">
                        <i class="fas fa-sync-alt"></i> {{-- Subscription --}}
                        <p>Subscription</p>
                    </a>
                </li>
                <li class="nav-item ml-3">
                    <a href="#">
                        <i class="fas fa-ticket-alt"></i> {{-- Ticket --}}
                        <p>Ticket</p>
                    </a>
                </li>
                <li class="nav-item ml-3">
                    <a href="#">
                        <i class="fas fa-bullhorn"></i> {{-- Broadcast --}}
                        <p>Broadcast</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'announcement' ? 'active' : '' }}">
                    <a href="{{ route('announcement') }}">
                        <i class="fas fa-bell"></i> {{-- Announcement --}}
                        <p>Announcement</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'web-config' ? 'active' : '' }}">
                    <a href="{{ route('web-config') }}">
                        <i class="fas fa-cogs"></i>
                        <p>Setting Web</p>
                    </a>
                </li>

                {{-- TRANSACTION --}}
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">TRANSACTION</h4>
                </li>
                <li class="nav-item ml-3">
                    <a href="#">
                        <i class="fas fa-file-invoice-dollar"></i> {{-- Invoice --}}
                        <p>Invoice</p>
                    </a>
                </li>

                {{-- LOGOUT --}}
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Logout</h4>
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
