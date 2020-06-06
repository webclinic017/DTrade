@extends('layouts.app')

@section('title', 'Home')

@section('head')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/4.3.0/echarts.min.js" charset="utf-8"></script>
    <link rel="stylesheet" href="{{ mix('/css/home.css') }}">
@endsection

@section('body')
    <el-row :gutter="20">
        <el-col :span="16">
            <el-card>
                <div slot="header">
                    <h3>Dashboard</h3>
                    <div>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
            </el-card>
        </el-col>
        <el-col :span="8">
            <dashboard-portfolios initial_portfolios="{{ $portfolio }}"></dashboard-portfolios>
        </el-col>
    </el-row>
</div>
@endsection
