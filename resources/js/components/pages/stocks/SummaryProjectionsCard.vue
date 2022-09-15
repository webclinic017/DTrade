<template>
    <div class="stock-card bright-background material-shadow">
        <div class="stock-card__header">
            <h1>Projections</h1>
            <span>Last Updated {{ stock.quickLook.lastProjectionUpdate }}</span>
            <a v-if="!isRefreshing" @click="refreshProjections()" href="#">Update Now</a>
            <div v-else>
                <i class="el-icon-loading"></i>
            </div>
        </div>
        <div class="stock-card__projections">
            <div class="projection" v-for="timePeriod in timePeriods" :class="nDayClass(stock[timePeriod])">
                <h3 class="projection__time-period">{{ timePeriodsReadable[timePeriod] }}</h3>
                <h1>{{ stock[timePeriod].verdict }}</h1>
                <h3 class="projection__accuracy">{{ stock[timePeriod].accuracy }}% Historical Accuracy</h3>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['passedStock'],
        data () {
            return {
                stock: this.passedStock,
                isRefreshing: false,
                timePeriods: ['nextDay', 'fiveDay', 'tenDay'],
                timePeriodsReadable: {'nextDay': 'next day', 'fiveDay': 'five day', 'tenDay': 'ten day'}
            };
        },
        methods: {
            nDayClass: function (day) {
                return day.verdict;
            },
            refreshProjections: function () {
                this.isRefreshing = true;
                axios.post(`/api/stocks/${this.stock.symbol}/analyze`)
                    .then(() => {
                        setTimeout(() => {
                            this.isRefreshing = false;
                        }, 3000);
                    })
                    .catch((e) => {
                        this.isRefreshing = false;
                        this.$notify({
                            title: 'Error',
                            message: e.message,
                            type: 'error',
                            duration: 2000
                        })
                    });
            }
        },
        mounted () {
            Echo.channel('stocks')
                .listen('StockUpdated', (result) => {
                    if (this.stock.symbol === result.stock.symbol) {
                        this.isRefreshing = false;
                        this.stock = result.stock;
                    }
                })
        }
    }
</script>

<style scoped lang="scss">
    @import '../../../../sass/variables';

    .stock-card {
        padding: 10px 15px;
        border-radius: 5px;
        color: $dark;

        h1 {
            display: inline-block;
            font-weight: normal;
            font-size: 40px;
            margin: 0;
        }

        &__header {
            padding-bottom: 10px;
            position: relative;

            h1 {
                padding-left: 10px;
            }

            span {
                padding-left: 10px;

                @media screen and (max-width: 820px) {
                    position: absolute;
                    bottom: -10px;
                    left: 0;
                }
            }

            a, div {
                display: inline-block;
                float: right;
                margin-top: 23px;
                color: $dark;
                padding-right: 10px;
            }
        }

        &__projections {
            width: 100%;
            padding-top: 10px;
            padding-bottom: 9px;
            overflow: hidden;

            .projection {
                text-align: center;
                width: calc(33.33% - 22px);
                border: 1px solid $body-bg;
                border-bottom: 15px solid;
                border-radius: 5px;
                margin-right: 10px;
                margin-left: 10px;
                float: left;

                @media screen and (max-width: 820px) {
                    width: calc(100% - 22px);
                    margin-top: 20px;
                }

                &.profit {
                    &.large, &.moderate {
                        border-bottom-color: $profit-green;
                    }
                    &.small {
                        border-bottom-color: darken($profit-yellow, 7%);
                    }
                }

                &.loss {
                    border-bottom-color: $loss-red;
                }

                h1 {
                    word-spacing: 100vw;
                    text-transform: capitalize;
                }

                h3 {
                    font-weight: normal;
                }

                &__time-period {
                    text-transform: uppercase;
                    border-bottom: 1px solid $body-bg;
                    padding-bottom: 10px;
                }

                &__accuracy {
                    padding-left: 10px;
                    padding-right: 10px;
                    margin-bottom: 20px;
                    margin-top:  13px;
                }
            }
        }
    }
</style>





