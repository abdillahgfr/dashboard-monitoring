<!-- start: header -->
<header class="header">
    <div class="logo-container">
        <a href="/notifikasi" class="logo">
            <h1 style="margin-top: 0px">EPersediaan</h1>
        </a>
        <div class="d-md-none toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html"
            data-fire-event="sidebar-left-opened">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div>

    <!-- start: search & user box -->
    <div class="header-right">

        <span class="separator"></span>

        <div id="userbox" class="userbox">
            <a href="{{ route('home') }}" data-bs-toggle="dropdown">
                <figure class="profile-picture">
                    <img src="img/!logged-user.jpg" alt="Joseph Doe" class="rounded-circle"
                        data-lock-picture="img/!logged-user.jpg" />
                </figure>
                <div class="profile-info" data-lock-name="John Doe" data-lock-email="johndoe@okler.com">
                    <span class="name">Akun Monitoring</span>
                    <span class="role">Administrator</span>
                </div>

                <i class="fa custom-caret"></i>
            </a>

            <div class="dropdown-menu">
                <ul class="list-unstyled mb-2">
                    <li class="divider"></li>
                    <li>
                        <a role="menuitem" tabindex="-1" href="{{ route('login') }}"><i class="bx bx-power-off"></i>
                            Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- end: search & user box -->
</header>
<!-- end: header -->

<!-- start: sidebar -->

<aside id="sidebar-left" class="sidebar-left">

    <div class="sidebar-header">
        <div class="sidebar-title">
            MENU
        </div>
        <div class="sidebar-toggle d-none d-md-block" data-toggle-class="sidebar-left-collapsed" data-target="html"
            data-fire-event="sidebar-left-toggle">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div>

    <div class="nano">
        <div class="nano-content">
            <nav id="menu" class="nav-main" role="navigation">
                <ul class="nav nav-main">
                    {{-- <li class="{{ request()->routeIs('home') ? 'nav-active' : '' }}">
                        <a class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}"
                            href="{{ route('home') }}">
                            <i class="bx bx-cube" aria-hidden="true"></i>
                            <span>Persediaan</span>
                        </a>
                    </li> --}}
                    <li class="{{ request()->is('notifikasi') ? 'nav-active' : '' }}">
                        <a class="nav-link {{ request()->is('notifikasi') ? 'nav-link-active' : '' }}"
                            href="/notifikasi">
                            <i class="bx bx-file" aria-hidden="true"></i>
                            <span>Persediaan</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <hr class="separator" />
        </div>
        <script>
            // Maintain Scroll Position
            if (typeof localStorage !== 'undefined') {
                if (localStorage.getItem('sidebar-left-position') !== null) {
                    var initialPosition = localStorage.getItem('sidebar-left-position'),
                        sidebarLeft = document.querySelector('#sidebar-left .nano-content');

                    sidebarLeft.scrollTop = initialPosition;
                }
            }
        </script>
    </div>
</aside>
<!-- end: sidebar -->
