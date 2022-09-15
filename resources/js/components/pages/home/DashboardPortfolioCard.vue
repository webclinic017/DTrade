<template>
    <el-card class="portfolio" shadow="hover">
        <div slot="header">
            <h3 class="capitalize">
                {{ portfolio.title }}
                <i :class="iconClass + ' circle padding transition material-shadow right'" @click="refresh"></i>
            </h3>
        </div>
        <p>Value: <strong>${{ portfolio.value }}</strong></p>
        <span>Last Updated {{ portfolio.updated_at }}</span>
    </el-card>
</template>

<script>
    import ElementUI from 'element-ui'
    export default {
        name: "DashboardPortfolioCard",
        props: ['sentPortfolio', 'userId'],
        components: {
            ElementUI,
        },
        data () {
            return {
                portfolio: this.sentPortfolio,
                loading: false
            }
        },
        computed: {
            iconClass () {
                return this.loading ?
                    'el-icon-loading' :
                    'el-icon-refresh cursor material-hover';
            }
        },
        methods: {
            refresh () {
                if (!this.loading) {
                    this.$http.get('api/process/refresh').then(() => {
                        this.loading = true;
                    }).catch((error) => {
                        this.$notify.error({
                            title: 'Error',
                            message: error.body.message
                        });
                    });
                }
            }
        },
        mounted () {
            Echo.private(`user.${ this.userId }`)
                .listen('Portfolio.PortfolioUpdated', (payload) => {
                    this.portfolio = payload.message;
                    this.loading = false;
                });
        }
    }
</script>

<style scoped lang="scss">
    .portfolio {

        .el-card__header {

            padding-top: 5px;
            padding-bottom: 5px;

        }

        i {

            border: none;
            margin-top: -10px;

        }

        p {

            margin-top: 0;
            margin-bottom: 0.5em;
        }

        span {

            font-size: 0.7em;

        }

    }
</style>
