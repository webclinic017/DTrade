@extends('layouts.profile')

@section('title', 'Profile - Alpha Vantage')

@section('nav-active-index', '2')

@section('page-header')
    <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item><a href="/profile/">Profile</a></el-breadcrumb-item>
        <el-breadcrumb-item>Alpha Vantage</el-breadcrumb-item>
    </el-breadcrumb>
@endsection

@section('content')
    <el-card shadow="never">
        <div class="margin-bottom">
            <p>
                DTrade uses a service Called Alpha Vantage to get most of its market data throughout the day for
                each and every stock on our site. In order to keep DTrade free, before you can add your own stocks
                for us to analyze, you need to supply an API key for us to use to get the data. This is because the
                free data API they provide is only able to process so many updates every minute, and every day.
            </p>
            <p>
                In the future we hope to grow to a place where we no longer require our users to do this, but right now
                it is necessary to keep the service free and fast for everyone. To obtain your API Key, visit their site
                <a href="https://www.alphavantage.co/support/#api-key" target="_blank">here</a>.
            </p>
        </div>
        <el-form method="POST" action="{{ route('profile.alpha-vantage.save') }}">
            @csrf
            <el-form-item label="Alpha Vantage API Key">
                <div class="el-input">
                    <input id="api-key" type="text" class="el-input__inner" name="api-key" value="{{ $apiKey }}" required>
                </div>
                <span class="dim-text">Last Updated: {{ $updatedAt }}</span>
            </el-form-item>
            <button type="submit" class="el-button el-button--primary">Save</button>
        </el-form>
    </el-card>
@endsection
