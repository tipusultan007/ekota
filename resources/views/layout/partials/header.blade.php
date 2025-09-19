<nav class="navbar">
  <div class="navbar-content">

    <div class="logo-mini-wrapper">
      <img src="{{ url('build/images/logo-mini-light.png') }}" class="logo-mini logo-mini-light" alt="logo">
      <img src="{{ url('build/images/logo-mini-dark.png') }}" class="logo-mini logo-mini-dark" alt="logo">
    </div>

    <form class="search-form">
      <div class="input-group">
        <div class="input-group-text">
          <i data-lucide="search"></i>
        </div>
        <input type="text" class="form-control" id="navbarForm" placeholder="Search here...">
      </div>
    </form>

    <ul class="navbar-nav">
      <li class="theme-switcher-wrapper nav-item">
        <input type="checkbox" value="" id="theme-switcher">
        <label for="theme-switcher">
          <div class="box">
            <div class="ball"></div>
            <div class="icons">
              <i class="link-icon" data-lucide="sun"></i>
              <i class="link-icon" data-lucide="moon"></i>
            </div>
          </div>
        </label>
      </li>
        {{-- Language Dropdown --}}
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                @if(session()->get('locale') == 'bn')
                    <img src="{{ url('build/images/flags/bd.svg') }}" class="w-20px" title="bn" alt="flag">
                    <span class="ms-2 d-none d-md-inline-block">বাংলা</span>
                @else
                    <img src="{{ url('build/images/flags/us.svg') }}" class="w-20px" title="us" alt="flag">
                    <span class="ms-2 d-none d-md-inline-block">English</span>
                @endif

            </a>
            <div class="dropdown-menu" aria-labelledby="languageDropdown">
                {{-- English Link --}}
                <form action="{{ route('language.switch') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="lang" value="en">
                    <button type="submit" class="dropdown-item py-2 d-flex border-0 bg-transparent">
                        <img src="{{ url('build/images/flags/us.svg') }}" class="w-20px" title="us" alt="us">
                        <span class="ms-2"> English </span>
                    </button>
                </form>

                {{-- Bengali Link --}}
                <form action="{{ route('language.switch') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="lang" value="bn">
                    <button type="submit" class="dropdown-item py-2 d-flex border-0 bg-transparent">
                        <img src="{{ url('build/images/flags/bd.svg') }}" class="w-20px" title="bn" alt="bn">
                        <span class="ms-2"> বাংলা </span>
                    </button>
                </form>
            </div>
        </li>

      {{--<li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i data-lucide="bell"></i>
          <div class="indicator">
            <div class="circle"></div>
          </div>
        </a>
        <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown">
          <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
            <p>6 New Notifications</p>
            <a href="javascript:;" class="text-secondary">Clear all</a>
          </div>
          <div class="p-1">
            <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
              <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                <i class="icon-sm text-white" data-lucide="gift"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p>New Order Recieved</p>
                <p class="fs-12px text-secondary">30 min ago</p>
              </div>
            </a>
            <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
              <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                <i class="icon-sm text-white" data-lucide="alert-circle"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p>Server Limit Reached!</p>
                <p class="fs-12px text-secondary">1 hrs ago</p>
              </div>
            </a>
            <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
              <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                <img class="w-30px h-30px rounded-circle" src="{{ url('https://placehold.co/30x30') }}" alt="userr">
              </div>
              <div class="flex-grow-1 me-2">
                <p>New customer registered</p>
                <p class="fs-12px text-secondary">2 sec ago</p>
              </div>
            </a>
            <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
              <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                <i class="icon-sm text-white" data-lucide="layers"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p>Apps are ready for update</p>
                <p class="fs-12px text-secondary">5 hrs ago</p>
              </div>
            </a>
            <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
              <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                <i class="icon-sm text-white" data-lucide="download"></i>
              </div>
              <div class="flex-grow-1 me-2">
                <p>Download completed</p>
                <p class="fs-12px text-secondary">6 hrs ago</p>
              </div>
            </a>
          </div>
          <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
            <a href="javascript:;">View all</a>
          </div>
        </div>
      </li>--}}
        {{-- Profile Dropdown --}}
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img class="w-30px h-30px ms-1 rounded-circle" src="{{ url('https://placehold.co/30x30') }}" alt="profile">
            </a>
            <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                    <div class="mb-3">
                        <img class="w-80px h-80px rounded-circle" src="{{ url('https://placehold.co/80x80') }}" alt="">
                    </div>
                    <div class="text-center">
                        <p class="fs-16px fw-bolder">{{ auth()->user()->name }}</p>
                        <p class="fs-12px text-secondary">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <ul class="list-unstyled p-1">
                    <li>
                        {{-- এই লিঙ্কটি পরিবর্তন করুন --}}
                        <a href="{{ route('profile.edit') }}" class="dropdown-item py-2 text-body ms-0">
                            <i class="me-2 icon-md" data-lucide="user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <!-- Logout Form -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}" class="dropdown-item py-2 text-body ms-0"
                               onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="me-2 icon-md" data-lucide="log-out"></i>
                                <span>{{ __('Log Out') }}</span>
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
        </li>
    </ul>

    <a href="#" class="sidebar-toggler">
      <i data-lucide="menu"></i>
    </a>

  </div>
</nav>
