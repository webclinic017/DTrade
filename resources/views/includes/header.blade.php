<el-menu id="header" mode="horizontal">
    <el-menu-item onclick="window.location.href='{{ url("/") }}'">
        <img id="logo" src="/logo.png" title="{{ config('app.name', 'Laravel') }}" alt="{{ config('app.name', 'Laravel') }}" />
    </el-menu-item>
    @guest
        <el-menu-item onclick='window.location.href="{{ route('login') }}"'>
           Login
        </el-menu-item>
        @if (Route::has('register'))
            <el-menu-item onclick='window.location.href="{{ route('register') }}"'>
                Register
            </el-menu-item>
        @endif
    @else
        <el-submenu index="1" style="margin-left: 36px">
            <template slot="title">{{ Auth::user()->name }}</template>
            <el-menu-item onclick="window.location.href='{{ route('profile.index') }}'">
                Profile
            </el-menu-item>
            <el-menu-item onclick="document.getElementById('logout-form').submit()">
                Logout
            </el-menu-item>
        </el-submenu>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    @endguest
</el-menu>
