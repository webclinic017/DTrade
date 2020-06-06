@extends('layouts.profile')

@section('title', 'Profile - Robinhood')

@section('nav-active-index', '1')

@section('page-header')
    <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item><a href="/profile/">Profile</a></el-breadcrumb-item>
        <el-breadcrumb-item>Robinhood</el-breadcrumb-item>
    </el-breadcrumb>
@endsection

@section('content')
    <el-card shadow="never">
        <div class="margin-bottom">
            <p>
                DTrade can perform stock trading for you automatically, and give you personalized recommendations for
                position sizes based on your portfolio value, if you provide your Robinhood account credentials. Your
                security is our top priority, your password will be encryped using the AES-256-CBC cipher. We are
                working on more ways to keep your information secure. In the future we will be implementing a
                pin-based key system, which would make encrypted passwords even more secure. This will be an
                optional security setting down the road, and we will keep you updated when it is released.
            </p>
        </div>
        <el-row>
            <el-col :md="8" :sm="24" class="center" style="text-align: left;">
                <el-form method="POST" action="{{ route('profile.robinhood.save') }}">
                    @csrf
                    <el-form-item label="Robinhood Username">
                        <div class="el-input">
                            <input id="username" type="text" class="el-input__inner" name="username" value="{{ $username }}" required>
                        </div>
                    </el-form-item>
                    <el-form-item label="Robinhood Password">
                        <div class="el-input">
                            <input id="password" type="password" class="el-input__inner" name="password" required>
                        </div>
                        <span class="dim-text">Last Updated: {{ $updatedAt }}</span>
                    </el-form-item>
                    <el-form-item>
                        <button type="submit" class="el-button el-button--primary">Save</button>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
    </el-card>
@endsection
