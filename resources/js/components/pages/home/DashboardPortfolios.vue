<template>
    <el-container class="portfolios">
        <el-header>
            <h1>Portfolio</h1>
        </el-header>
        <el-main>
            <dashboard-portfolio-card
                v-for="(portfolio, index) in portfolios"
                :key="'pc-'+index+'-'+portfolio.updated_at"
                :sent-portfolio="portfolio"
                :user-id="userId"
            >
            </dashboard-portfolio-card>
        </el-main>
    </el-container>
</template>

<script>
    export default {
        name: "DashboardPortfolios",
        props: ["initial_portfolios", 'userId'],
        data () {
            return {
                portfolios: [],
            };
        },
        methods: {
            updatePortfolios (portfolios) {
                this.portfolios = portfolios;
            }
        },
        mounted () {
            let portfolios = JSON.parse(this.initial_portfolios);

            portfolios = Array.isArray(portfolios) ? portfolios : [portfolios];
            this.updatePortfolios(portfolios);
        }
    }
</script>

<style scoped>

</style>
