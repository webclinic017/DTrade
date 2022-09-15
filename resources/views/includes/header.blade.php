<div id="header">
    <nav>
        <ul class="left">
            <li>
                <a href="{{ route('home') }}">
                    <img id="logo" src="/logo.png" title="{{ config('app.name', 'Laravel') }}" alt="{{ config('app.name', 'Laravel') }}" />
                </a>
            </li>
        </ul>
        <ul class="right">
            @auth
                <div class="mobile">
                    <el-menu mode="horizontal">
                        <el-submenu index="1" style="margin-left: 36px">
                            <template slot="title"><i class="el-icon-menu"></i></template>
                            @if(!Route::is('stocks.all'))
                                <el-menu-item onclick="window.location.href='{{ route('stocks.all') }}'">
                                    Stocks
                                </el-menu-item>
                            @endif
                            @if(!Route::is('profile.*'))
                                <el-menu-item onclick="window.location.href='{{ route('profile.index') }}'">
                                    Profile
                                </el-menu-item>
                            @endif
                            <el-menu-item onclick="document.getElementById('logout-form').submit()">
                                Logout
                            </el-menu-item>
                        </el-submenu>
                    </el-menu>
                </div>
                <div class="desktop">
                    @if(!Route::is('stocks.all'))
                        <li><a href="{{ route('stocks.all') }}"><span>Stocks</span></a></li>
                    @endif
                    @if(!Route::is('profile.*'))
                        <li><a href="{{ route('profile.index') }}"><span>Profile</span></a></li>
                    @endif
                    <li><a href="#" onclick="document.getElementById('logout-form').submit()"><span>Logout</span></a></li>
                </div>
            @else
                <div class="mobile">
                    <el-menu mode="horizontal">
                        <el-submenu index="1" style="margin-left: 36px">
                            <template slot="title"><i class="el-icon-menu"></i></template>
                            <el-menu-item onclick="window.location.href='{{ route('login') }}'">
                                Login
                            </el-menu-item>
                            <el-menu-item onclick="window.location.href='{{ route('register') }}'">
                                Register
                            </el-menu-item>
                        </el-submenu>
                    </el-menu>
                </div>
                <div class="desktop">
                    <li><a href="{{ route('login') }}"><span>Login</span></a></li>
                    <li><a href="{{ route('register') }}"><span>Register</span></a></li>
                </div>
            @endauth
        </ul>
    </nav>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    @auth
        <event-receiver :user-id="{{ Auth::user()->id }}"></event-receiver>
    @endauth
</div>
