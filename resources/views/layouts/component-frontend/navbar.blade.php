<!-- Spinner Start -->
<div id="spinner"
    class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>
<!-- Spinner End -->


<!-- Navbar & Hero Start -->
<div class="container-fluid sticky-top px-0">
    <div class="position-absolute bg-dark" style="left: 0; top: 0; width: 100%; height: 100%;">
    </div>
    <div class="container px-0">
        <nav class="navbar navbar-expand-lg navbar-dark  py-3 px-4" style="background-color: white">
            <a href="{{ url('/') }}" class="navbar-brand p-0">
                <img src="{{ asset('assets/images/logo.png') }}" alt="logo" style="width: 40%; height: auto;">
                <!-- <img src="img/logo.png" alt="Logo"> -->
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="{{ url('/') }}"
                        class="nav-item nav-link {{ request()->is('/') ? 'active' : '' }}">Beranda</a>
                    <a href="{{ route('booking.create') }}"
                        class="nav-item nav-link {{ request()->is('booking/create') ? 'active' : '' }}">Booking</a>
                    <a href="{{ route('booking_ruangan') }}"
                        class="nav-item nav-link {{ request()->is('booking/ruangan') ? 'active' : '' }}">Ruangan</a>
                    @auth
                        <a href="{{ route('booking_riwayat') }}"
                            class="nav-item nav-link {{ request()->is('booking/riwayat') ? 'active' : '' }}">Profile</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        <a href="#" class="nav-item nav-link"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                    @endauth
                    @guest
                        <a href="{{ route('login') }}"
                            class="nav-item nav-link {{ request()->is('login') ? 'active' : '' }}">Login</a>
                    @endguest
                </div>
            </div>
        </nav>
    </div>
</div>
<!-- Navbar & Hero End -->
