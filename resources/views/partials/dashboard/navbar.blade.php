<!-- Navbar Header -->
<nav class="navbar navbar-header navbar-expand-lg" data-background-color="{{ $topbarColor }}">
    <div class="container-fluid">
        @if ($user->role == 'teknisi')
            <h4>
                <i class="icon-wallet mr-3 text-white"></i>
                <b class="text-white" id="w1_balanceTeknisi">Rp. 0</b>
            </h4>
        @endif
        
        <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
        </ul>
    </div>
</nav>
