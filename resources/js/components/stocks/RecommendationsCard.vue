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
            <el-card shadow="never" class="inline-block card" ref="kellyPercent">
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
                <div v-else>

                </div>
            </div>
        </div>
    </el-card>
</template>

<script>
    export default {
        props: {
            'stock': { type: Object, required: true, },
            'portfolio': { type: Object, required: false, },
            'robinhood': { type: String, required: true, },
        },
        data () {
            return {
                width: 0,
                height: 0,
                innerHeight: 0,
                windowWidth: 0,
                period: 'average',
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
                this.innerHeight = this.$refs.personalized.children[0].children[0].clientHeight;
            },
            calculateDimensions () {
                this.calculateWidth();
                setTimeout(() => {
                    this.calculateHeight();
                    this.calculateInnerHeight();
                }, 0);
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
                if (this.period === 'average') {
                    return this.stock.averageKellySize;
                } else {
                    return this.stock[this.period].kellySize;
                }
            },
            hasPortfolio () {
                return this.portfolio !== null;
            },
        },
        mounted () {
            this.calculateDimensions();

            window.addEventListener('resize', (e) => {
                this.calculateDimensions();
            });

            window.src = this;
        }
    }
</script>

<style scoped lang="scss">
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
    }
</style>
