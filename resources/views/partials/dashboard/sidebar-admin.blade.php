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
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                {{-- <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">MASTER</h4>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'bank' ? 'active' : '' }}">
                    <a href="{{ route('bank') }}">
                        <i class="fas fa-building"></i>
                        <p>Bank</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'banner' ? 'active' : '' }}">
                    <a href="{{ route('banner') }}">
                        <i class="fas fa-image"></i>
                        <p>Banner</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'information' ? 'active' : '' }}">
                    <a href="{{ route('information') }}">
                        <i class="fas fa-info-circle"></i>
                        <p>Information</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'product-category' ? 'active' : '' }}">
                    <a href="{{ route('product-category') }}">
                        <i class="fas fa-tags"></i>
                        <p>Kategori Produk</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'akrab' ? 'active' : '' }}">
                    <a href="{{ route('akrab') }}">
                        <i class="fas fa-store"></i>
                        <p>Paket Akrab</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'product' ? 'active' : '' }}">
                    <a href="{{ route('product') }}">
                        <i class="fas fa-cubes"></i>
                        <p>List Produk</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'reward' ? 'active' : '' }}">
                    <a href="{{ route('reward') }}">
                        <i class="fas fa-trophy"></i>
                        <p>Reward</p>
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
                <li class="nav-item ml-3 {{ $routename == 'trx-upgrade' ? 'active' : '' }}">
                    <a href="{{ route('trx-upgrade') }}">
                        <i class="fas fa-arrow-up"></i>
                        <p>Upgrade Akun</p>
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
                        <p>Saldo Reseller</p>
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
                    <h4 class="text-section">MANAGE</h4>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'reseller' ? 'active' : '' }}">
                    <a href="{{ route('reseller') }}">
                        <i class="fas fa-user-tag"></i>
                        <p>Reseller</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'owner' ? 'active' : '' }}">
                    <a href="{{ route('owner') }}">
                        <i class="fas fa-user-tie"></i>
                        <p>Owner</p>
                    </a>
                </li>
                <li class="nav-item ml-3 {{ $routename == 'web-config' ? 'active' : '' }}">
                    <a href="{{ route('web-config') }}">
                        <i class="fas fa-cogs"></i>
                        <p>Setting Web</p>
                    </a>
                </li>
                <li class="nav-item ml-3">
                    <a href="{{ route('home') }}" target="__blank">
                        <i class="fas fa-arrow-left"></i>
                        <p>Website Page</p>
                    </a>
                </li> --}}
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
