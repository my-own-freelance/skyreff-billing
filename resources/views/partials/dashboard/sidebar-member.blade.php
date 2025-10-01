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
                <li class="nav-item ml-3 {{ $routename == 'invoice' ? 'active' : '' }}">
                    <a href="{{ route('invoice') }}">
                        <i class="fas fa-file-invoice-dollar"></i> {{-- Invoice --}}
                        <p>Invoice</p>
                    </a>
                </li>
                {{-- <li class="nav-item ml-3 {{ $routename == 'akrab' ? 'active' : '' }}">
                    <a href="{{ route('akrab') }}">
                        <i class="fas fa-store"></i>
                        <p>Paket Akrab</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'product' ? 'active' : '' }}">
                    <a href="{{ route('product') }}">
                        <i class="fas fa-cubes"></i>
                        <p>List Product</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'xlotp' ? 'active' : '' }}">
                    <a href="{{ route('xlotp') }}">
                        <i class="fas fa-spinner"></i>
                        <p>XL OTP</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Transaction</h4>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'trx-topup' ? 'active' : '' }}">
                    <a href="{{ route('trx-topup') }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <p>Topup Saldo</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'trx-product' ? 'active' : '' }}">
                    <a href="{{ route('trx-product') }}">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Product</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'trx-commission' ? 'active' : '' }}">
                    <a href="{{ route('trx-commission') }}">
                        <i class="fas fa-wallet"></i>
                        <p>Tarik Komisi</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'trx-debt' ? 'active' : '' }}">
                    <a href="{{ route('trx-debt') }}">
                        <i class="fas fa-exchange-alt"></i>
                        <p>Pihutang</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'trx-compensation' ? 'active' : '' }}">
                    <a href="{{ route('trx-compensation') }}">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Komplain Transaksi</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'trx-reward' ? 'active' : '' }}">
                    <a href="{{ route('trx-reward') }}">
                        <i class="fas fa-gift"></i>
                        <p>Hadiah</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Mutation</h4>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'mutation-topup' ? 'active' : '' }}">
                    <a href="{{ route('mutation-topup') }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <p>Saldo Akun</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'mutation-commission' ? 'active' : '' }}">
                    <a href="{{ route('mutation-commission') }}">
                        <i class="fas fa-wallet"></i>
                        <p>Commission</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Management</h4>
                </li>
                <li class="nav-item ml-3">
                    <a href="{{ route('reseller.account') }}">
                        <i class="fas fa-user-cog"></i>
                        <p>Setting Account</p>
                    </a>
                </li>
                <li class="nav-item ml-3">
                    <a href="{{ route('home') }}" target="__blank">
                        <i class="fas fa-arrow-left"></i>
                        <p>Website Page</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Logout</h4>
                </li> --}}
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
