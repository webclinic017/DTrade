@extends('layouts.app')

@section('title', 'Home')

@section('head')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/4.3.0/echarts.min.js" charset="utf-8"></script>
    <link rel="stylesheet" href="{{ mix('/css/home.css') }}">
@endsection

@section('body')
        <el-row :gutter="20">
            <el-col :md="{{ $portfolio ? '16' : '24' }}" :sm="24">
                <el-container class="home">
                    <el-header>
                        <h1>Watchlist</h1>
                    </el-header>
                    <el-main>
                        <div class="card-body">
                            @if ($watchlist && $watchlist->stocks)
                                @foreach($watchlist->stocks as $stock)
                                    <el-row>
                                        <el-col>
                                            <stock-row-card :passed-stock="{{ $stock }}" :is-in-watchlist="true" style="border: 1px solid #ccc"></stock-row-card>
                                        </el-col>
                                    </el-row>
                                @endforeach
                            @else
                                <p>Your watchlist is currently empty.</p>
                            @endif
                        </div>
                    </el-main>
                </el-container>
            </el-col>
            <el-col :md="8" :sm="24" class="{{ $portfolio ? '' : 'hidden' }}">
                <dashboard-portfolios user-id="{{ $user->id }}" initial_portfolios="{{ $portfolio }}"></dashboard-portfolios>
            </el-col>
        </el-row>
</div>
@endsection
