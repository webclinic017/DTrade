@extends('layouts.app')

@section('head')
    <link rel="stylesheet" href="{{ mix('/css/profile.css') }}">
@endsection

@section('body')
    <div class="profile">
        <el-container style="border: 1px solid #eee; height: calc(100vh - 120px);">
            <el-aside width="auto">
                <profile-nav
                    default-activated="@yield('nav-active-index')"
                    default-opened="@yield('nav-open-index')"
                    page-links="{{ $pageLinks }}"
                >
                </profile-nav>
            </el-aside>
            <el-container>
                <el-header style="text-align: right; font-size: 12px">
                    <h1>@yield('page-header')</h1>
                </el-header>
                <el-main>
                    @yield('content')
                </el-main>
            </el-container>
        </el-container>
    </div>
@endsection
