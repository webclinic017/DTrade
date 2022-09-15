<template>
    <el-card shadow="never" ref="recommendations" class="recommendations">
        <div slot="header" class="overflow-hidden">
            <h3 class="left">Recommendations</h3>
            <div class="right select">
                <el-select v-model="period">
                    <el-option value="average" label="Overall" selected="selected"></el-option>
                    <el-option value="nextDay" label="Next Day"></el-option>
                    <el-option value="fiveDay" label="Five Day"></el-option>
                    <el-option value="tenDay" label="Ten Day"></el-option>
                </el-select>
            </div>
        </div>
        <div class="cards">
            <el-card shadow="never" class="inline-block card" ref="kellyPercent" style="padding-top: 16px; padding-bottom: 16px">
                <el-progress type="dashboard" :percentage="percentage" color="#99E1D9" :width="innerWidth" :stroke-width="10"></el-progress>
                <div class="text-container" :style="'width: ' + innerWidth + 'px'">
                    <p>Of your portfolio should consist of this stock.</p>
                    <span>Calculation based on the <a href="https://blogs.cfainstitute.org/investor/2018/06/14/the-kelly-criterion-you-dont-know-the-half-of-it/" class="transition">Kelly Criteron</a> and our <a href="#" class="transition">projections</a></span>
                </div>
            </el-card>
            <div :style="'width: ' + remainingWidth + 'px; height: ' + height + 'px'" :class="(hasPortfolio ? '' : 'greyed ') + 'card actions'" ref="personalized">
                <div v-if="!hasPortfolio">
                    <p :style="'margin-top: ' + ((height - innerHeight) / 2) + 'px'">To access automated ordering and share balancing, you need to <a :href="robinhood">link your Robinhood account.</a></p>
                </div>
                <el-card v-else shadow="never" class="kelly-actions card" ref="actions">
                    <div class="vertical-center inline-block" :style="'margin-top: ' + actionsMargin + 'px; margin-bottom: ' + actionsMargin + 'px'">
                        <p>Based on your current Robinhood Portfolio, with a value of <span class="text-dark">${{ portfolio.value}}</span>, here is our recommendation:</p>
                        <div class="text-left inline-block center">
                            <h4>Optimal Position: <span class="highlight">{{ optimalPosition }} share{{ optimalPosition === 1 ? '' : 's'}}</span></h4>
                            <h4>Current Position: <span>{{ stock.currentPosition }} share{{ stock.currentPosition === 1 ? '' : 's'}}</span></h4>
                            <span class="dim-text">
                                Verdict:&nbsp;
                                <el-button v-if="shouldBuy || shouldSell" type="text" @click="openBuyOrSellModal()"><h3>{{ shouldBuy ? 'Buy' :'Sell'}} This Stock</h3></el-button>
                                <span v-else>Maintain Current Position</span>
                            </span>
                        </div>
                        <el-card shadow="never" class="inline-block padding margin">
                            <el-switch
                                v-model="automate"
                                active-text="Automated Position Balancing"
                                inactive-text="Manual Trading"
                                @change="openAutomationDialog">
                            </el-switch>
                        </el-card>
                        <el-dialog
                            title="Automated Position Balancing"
                            :visible.sync="automatedSizingDialogVisible"
                            class="position-balancing-modal"
                            @close="undoChangeToToggle"
                            width="50%">
                            <div v-if="automate">
                                <p><span>Warning: </span>Enabling position automation may result in some undesirable trades occurring. By enabling this feature, you are acknowledging this fact and agree to not hold us accountable to any losses endured as a result of these automated trades.</p>
                                <p class="padding-top">This feature aims to maintain the Kelly-Optimal position size of your holdings in this stock. It will attempt to make trades at optimal times for both selling and buying, but if one is not detected before the end of the day at which the optimal number of shares changes, a trade will be made at a locally-optimum price. Trades are tracked and executed optimally for your portfolio, and will only execute the best 3 trades in any given 5 day trading period.</p>
                                <div slot="footer" class="dialog-footer">
                                    <el-button @click="automatedSizingDialogVisible = false" class="margin-top">Never mind, I dont want the risk.</el-button>
                                    <el-button @click="modifyPositionAutomation(true)" type="success" class="margin-top">Automate my position!</el-button>
                                </div>
                            </div>
                            <div v-else>
                                <p>Are you sure you want to disable position automation?</p>
                                <div slot="footer" class="dialog-footer">
                                    <el-button @click="automatedSizingDialogVisible = false"  class="margin-top">No, keep it on.</el-button>
                                    <el-button @click="modifyPositionAutomation(false)" type="primary" class="margin-top">Disable it!</el-button>
                                </div>
                            </div>
                        </el-dialog>
                        <el-dialog
                            :title="(shouldBuy ? 'Buy' : 'Sell') + ' ' + stock.symbol + ' Stock'"
                            :visible.sync="buyOrSellDialogVisible"
                            class="buy-sell-modal"
                            width="50%">
                            <div>
                                <el-form>
                                    <el-form-item>
                                        <el-input-number v-model="orderSize" :min="1"></el-input-number>
                                    </el-form-item>
                                </el-form>
                                <div slot="footer" class="dialog-footer">
                                    <el-button @click="buyOrSellDialogVisible = false"  class="margin-top">Close</el-button>
                                    <el-button @click="buyOrSell()" type="success" class="margin-top">Submit Order</el-button>
                                </div>
                            </div>
                        </el-dialog>
                    </div>
                </el-card>
            </div>
        </div>
    </el-card>
</template>

<script>
    export default {
        props: {
            'passedStock': { type: Object, required: true, },
            'portfolio': { type: Object, required: false, },
            'robinhood': { type: String, required: true, },
        },
        data () {
            return {
                stock: this.passedStock,
                width: 0,
                height: 0,
                innerHeight: 0,
                windowWidth: 0,
                actionsMargin: 0,
                period: 'average',
                automate: false,
                automatedSizingDialogVisible: false,
                buyOrSellDialogVisible: false,
                orderSize: 0,
            };
        },
        methods: {
            calculateWidth () {
                this.width = this.$refs.recommendations.$el.clientWidth;
                this.windowWidth = window.innerWidth;
            },
            calculateHeight () {
                this.height = this.$refs.kellyPercent.$el.clientHeight;
            },
            calculateInnerHeight () {
                this.innerHeight = this.$refs.personalized.children[0].clientHeight;
            },
            calculateDimensions () {
                this.calculateWidth();
                setTimeout(() => {
                    this.calculateHeight();
                    this.calculateInnerHeight();
                    this.kellyActionsCentering();
                    this.calculateWidth();
                }, 0);
            },
            openAutomationDialog () {
                this.automatedSizingDialogVisible = true;
            },
            undoChangeToToggle () {
                this.automate = !this.automate;
            },
            modifyPositionAutomation (enable) {
                this.automatedSizingDialogVisible = false;
                axios.post(`/api/automation/position-balancing/stock/${this.stock.symbol}/${enable ? 'enable' : 'disable'}`)
                    .then((result) => {
                        this.automate = enable;
                        this.$notify({
                            title: 'Success',
                            message: result.message,
                            type: 'success',
                            duration: 2000
                        });
                    })
                    .catch((e) => {
                        this.automate = !this.automate;
                        this.$notify({
                            title: 'Error',
                            message: e.message,
                            type: 'error',
                            duration: 2000
                        })
                    });
            },
            kellyActionsCentering () {
                let height = this.$refs.actions.$el.children[0].children[0].clientHeight;
                this.actionsMargin = (this.height - height - 40) / 2;
            },
            openBuyOrSellModal () {
                this.buyOrSellDialogVisible = true;
            },
            buyOrSell () {
                this.buyOrSellDialogVisible = false;
            },
        },
        computed: {
            innerWidth () {
                if (this.windowWidth > 1400) {
                    return (this.width / 3) - 82;
                } else if (this.windowWidth > 999) {
                    return (this.width / 2) - 82;
                } else {
                    if (this.width > 500) {
                        return (this.width / 2) - 82;
                    }
                    return this.width - 82;
                }
            },
            remainingWidth () {
                return this.width - this.innerWidth - 105;
            },
            percentage () {
                let kellySize;

                if (this.period === 'average') {
                    kellySize = this.stock.averageKellySize;
                } else {
                    kellySize = this.stock[this.period].kellySize;
                }

                return kellySize > 0 ? kellySize : 0;
            },
            hasPortfolio () {
                return this.portfolio !== null;
            },
            optimalPosition () {
                let moneyInStock = (this.percentage / 100) * this.portfolio.value,
                    numberShares = moneyInStock / this.stock.quickLook.price;

                return Math.round(numberShares);
            },
            shouldBuy () {
                return this.optimalPosition > this.stock.currentPosition;
            },
            shouldSell () {
                return this.optimalPosition < this.stock.currentPosition;
            },
            initialOrderSize () {
                return Math.abs(this.optimalPosition - this.passedStock.currentPosition);
            }
        },
        mounted () {
            this.calculateDimensions();

            window.addEventListener('resize', (e) => {
                this.calculateDimensions();
            });

            window.src = this;
            this.orderSize = this.initialOrderSize;

            Echo.channel('stocks')
                .listen('StockUpdated', (result) => {
                    if (this.stock.symbol === result.stock.symbol) {
                        let previousPosition = this.stock.currentPosition;
                        this.stock = result.stock;
                        this.stock.currentPosition = previousPosition;
                    }
                });
        }
    }
</script>

<style lang="scss">
    @import '../../../../sass/variables';

    .recommendations {
        .select {
            margin-top: 5px;
        }

        .cards {
            overflow: hidden;

            .card {
                float: left;
                clear: none;
            }

            .actions {
                margin-left: 18px;
                text-align: center;

                @media screen and (max-width: 582px) {
                    margin-left: 0;
                    margin-top: 18px;
                }

                &.greyed {
                    background-color: rgba(0,0,0,0.3);
                    color: #fff;

                    p {
                        padding: 10px;
                    }

                    & a{
                        color: #99E1D9;
                        text-decoration: none;

                        &:hover {
                            text-decoration: underline;
                        }
                    }
                }

                & .el-button {
                    h3 {
                        margin: 0;
                    }

                    &.margin-top {
                        margin-top: 10px !important;
                    }
                }

                .kelly-actions {
                    h4, p {
                        color: $dim;
                        font-weight: normal;

                        span:not(.highlight) {
                            color: $dark;
                        }
                    }

                    p {
                        width: calc(100% - 30px);
                        margin: 0 auto;
                    }

                    .el-switch__label {
                        height: auto !important;
                    }
                }
            }

            .text-container {
                text-align: center;

                p {
                    font-size: 16px;
                }

                span {
                    color: #787878;
                    font-size: 12px;

                    a {
                        color: #99E1D9;
                        opacity: 0.75;
                        text-decoration: none;

                        &:hover {
                            opacity: 1;
                            text-decoration: underline
                        }
                    }
                }
            }
        }

        .position-balancing-modal {
            p {
                text-wrap: normal;
                word-break: keep-all;

                span {
                    font-weight: bold;
                }

                &:first-of-type {
                    margin-top: 0;
                }
                &:last-of-type {
                    margin-bottom: 20px;
                }
            }
        }

        .buy-sell-modal {
            .el-icon {
                span {
                    line-height: 38px;
                }
            }
        }
    }
</style>
