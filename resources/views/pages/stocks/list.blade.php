@extends('layouts.stocks')

@section('title', 'All Stocks')

@section('content')
    <el-container>
        <el-header>
            <h1>All Stocks</h1>
        </el-header>
        <el-main>
            <el-row :gutter="20">
                <el-col>
                    @foreach($stocks as $stock)
                        <el-row>
                            <el-col>
                                <stock-row-card :passed-stock="{{ $stock }}" :is-in-watchlist="{{ $stock->inWatchlist }}"></stock-row-card>
                            </el-col>
                        </el-row>
                    @endforeach
                </el-col>
            </el-row>
        </el-main>
    </el-container>
@endsection
