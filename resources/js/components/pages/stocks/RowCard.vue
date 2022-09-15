<template>
    <div class="light-background stock-card material-hover cursor transition">
        <el-row>
            <div @click="goToStock">
                <el-col :sm="14" :xs="24">
                    <div class="stock-card__margin-wrapper top-border">
                        <h1>{{ stock.symbol }}</h1>
                        <h4>Last Update<br />{{ stock.quickLook.lastUpdate }}</h4>
                    </div>
                    <div class="stock-card__margin-wrapper bottom-border">
                        <h1>${{ Number(stock.quickLook.price).toFixed(3) }}</h1>
                        <h4>{{ stock.quickLook.change }} ({{ stock.quickLook.changePercent }}%)</h4>
                    </div>
                </el-col>
                <el-col :sm="10" :xs="24">
                    <div class="stock-card__projections-wrapper">
                        <div v-for="timePeriod in timePeriods" :class="nDayClass(stock.quickLook[timePeriod])">
                            <h1>{{ getProfitLossChance(stock.quickLook[timePeriod]) }}%</h1>
                            <h2>chance</h2>
                            <h4>{{ timePeriodsReadable[timePeriod] }}<br />{{ getProfitLoss(stock.quickLook[timePeriod]) }}</h4>
                        </div>
                    </div>
                </el-col>
            </div>
        </el-row>
        <el-row class="stock-card__actions-container transition">
            <el-col class="center padding" :sm="12" :xs="24">
                <watchlist-button-component :stock="stock" :is-in-watchlist="isInWatchlist"></watchlist-button-component>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import WatchlistButtonComponent from "./WatchlistButtonComponent";
    export default {
        components: {WatchlistButtonComponent},
        props: ['passedStock', 'isInWatchlist'],
        data () {
            return {
                stock: this.passedStock,
                timePeriods: ['nextDay', 'fiveDay', 'tenDay'],
                timePeriodsReadable: {'nextDay': 'next day', 'fiveDay': 'five day', 'tenDay': 'ten day'}
            };
        },
        methods: {
            goToStock: function () {
                window.location.href = '/stocks/'+ this.stock.symbol;
            },
            nDayClass: function (day) {
                if ('profit' in day) {
                    return day.profit > 60 ? 'profit good-chance' : 'profit low-chance';
                } else {
                    return 'loss';
                }
            },
            getProfitLossChance: function (day) {
                return 'profit' in day ? day.profit : day.loss;
            },
            getProfitLoss: function (day) {
                return 'profit' in day ? 'profit' : 'loss';
            }
        },
        mounted () {
            Echo.channel('stocks')
                .listen('StockUpdated', (result) => {
                    if (this.stock.symbol === result.stock.symbol) {
                        this.stock = result.stock;
                    }
                });
        }
    }
</script>

<style scoped lang="scss">
    @import '../../../../sass/variables';
    @import '../../../../sass/formatting_classes';

    .stock-card {
        color: $dark;
        font-family: "Montserrat", sans-serif;
        border-radius: 5px;

        .el-col {
            margin: 0
        }

        &__margin-wrapper {

            margin-top: 10px;
            margin-bottom: 10px;
            display: inline-block;
            border-top: 1px solid $light;
            border-bottom: 1px solid $light;

            &.top-border {
                width: 35%;
                text-align: right;
                border-top-color: $dark;

                @media screen and (max-width: $mobile) {
                    width: calc(45% - 10px);
                }
            }

            &.bottom-border {
                width: 50%;
                text-align: left;
                float: right;
                border-bottom-color: $dark;
                margin-top: 10px;

                @media screen and (max-width: $mobile) {
                    width: calc(55% - 10px);
                }

                h4 {
                    height: 38px;
                }
            }

            h1 {
                margin: 0;
                font-size: 48px;

                @media screen and (max-width: $mobile) {
                    font-size: 40px;
                }
            }

            h4 {
                margin: 0;
                font-weight: lighter;
                text-transform: capitalize;
            }

        }

        &__projections-wrapper {
            div {
                width: 33.33%;
                height: 118px;
                float: left;
                text-align: center;
                overflow: hidden;

                &:last-of-type{
                    border-top-right-radius: 5px;
                    border-bottom-right-radius: 5px;
                }

                @media screen and (max-width: $mobile) {
                    &:last-of-type {
                        border-top-right-radius: 0;
                    }
                    &:first-of-type {
                        border-bottom-left-radius: 5px;
                    }
                }

                h1 {
                    font-size: 32px;
                    margin-bottom: 0;
                }

                h2 {
                    font-size: 14px;
                    margin-top: 0;
                    text-transform: uppercase;
                }

                h4 {
                    font-size: 10px;
                    margin-top: 15px;
                    text-transform: uppercase;
                }

                &.profit {
                    &.good-chance {
                        background-color: $profit-green;
                    }
                    &.low-chance {
                        background-color: $profit-yellow;
                    }
                }

                &.loss {
                    background-color: $loss-red;
                }
            }
        }

        &:hover {
            .stock-card__projections-wrapper div:last-of-type {
                border-bottom-right-radius: 0;
            }

            .stock-card__actions-container {
                display: block;
            }
        }

        &__actions-container {
            display: none;
            background-color: rgba(236, 236, 236, 0.81);
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;

            .center {
                margin: 0 auto;
            }
        }
    }
</style>
