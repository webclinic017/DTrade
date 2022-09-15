@extends('layouts.stocks')

@section('title', $stock->symbol)

@section('content')
    <el-container>
        <el-header>
            <h1>
                {{ $stock->symbol }}
                <span class="right" style="margin-top: 13px">
                    <watchlist-button-component :stock="{{ $stock }}" :is-in-watchlist="{{ $stock->inWatchlist }}"></watchlist-button-component>
                </span>
            </h1>
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><a href="/stocks">Stocks</a></el-breadcrumb-item>
                <el-breadcrumb-item>{{ $stock->symbol }}</el-breadcrumb-item>
            </el-breadcrumb>
        </el-header>
        <el-main>
            <el-row :gutter="20">
                <el-col :lg="10" :md="9" :sm="24">
                    <stock-today-card :passed-stock="{{ $stock }}"></stock-today-card>
                </el-col>
                <el-col :lg="14" :md="15" :sm="24">
                    <stock-summary-projections-card :passed-stock="{{ $stock }}"></stock-summary-projections-card>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :lg="14" :md="12">
                    <stock-recommendations-card
                        :passed-stock="{{ $stock }}"
                        :portfolio="{{ $portfolio }}"
                        robinhood="{{ route('profile.robinhood') }}">
                    </stock-recommendations-card>
                </el-col>
                <el-col :lg="10" :md="12">
                    <el-card shadow="never">
                        <h3 slot="header" class="">Detailed Projections</h3>
                        {!! $charts['projections']->container() !!}
                    </el-card>
                </el-col>
            </el-row>
            <el-row>
                <el-col>
                    <el-card>
                        {!! $charts['price']->container() !!}
                    </el-card>
                </el-col>
            </el-row>
            <el-row>
                <el-col>
                    <el-card>
                        {!! $charts['indicators']->container() !!}
                    </el-card>
                </el-col>
            </el-row>
            @if(array_key_exists('dataPoints', $charts))
            <el-row>
                <el-col>
                    <el-card>
                        <h3 slot="header" class="">Historical Indicator Visualization</h3>
                        {!! $charts['dataPoints']->container() !!}
                    </el-card>
                </el-col>
            </el-row>
            @endif
        </el-main>
    </el-container>
@endsection

@section ('scripts')
    @foreach ($charts as $chart)
        {!! $chart->script() !!}
    @endforeach
@endsection
<script>
    import WatchlistButtonComponent from "../../../js/components/pages/stocks/WatchlistButtonComponent";
    export default {
        components: {WatchlistButtonComponent}
    }
</script>
