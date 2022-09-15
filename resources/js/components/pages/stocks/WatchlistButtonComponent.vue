<template>
    <span>
        <el-button class="watchlist-button watchlist-button__desktop center" size="small" :icon="watchlistIcon" @click="modifyWatchlist" round>
            <span v-if="inWatchlist">Remove From Watchlist</span>
            <span v-else>Add To Watchlist</span>
        </el-button>
        <el-button
            class="watchlist-button hidden center"
            size="small"
            :title="inWatchlist ? 'Remove From Watchlist' : 'Add To Watchlist'"
            :icon="watchlistIcon"
            @click="modifyWatchlist"
            circle>
        </el-button>
    </span>
</template>

<script>
    export default {
        props: ['stock', 'isInWatchlist'],
        data () {
            return {
                inWatchlist: this.isInWatchlist,
            };
        },
        computed: {
            watchlistIcon: function () {
                return this.inWatchlist ? 'el-icon-minus' : 'el-icon-plus';
            }
        },
        methods: {
            modifyWatchlist: function () {
                let addOrRemove = this.inWatchlist ? 'remove' : 'add';
                axios.post(`/api/watchlist/stocks/${this.stock.symbol}/${addOrRemove}`)
                    .then((response) => {
                        this.inWatchlist = !this.inWatchlist;
                        this.$notify({
                            title: 'Success',
                            message: response.data,
                            type: 'success',
                            duration: 2000
                        });
                        if (window.location.href.indexOf('home') > -1) {
                            window.location.reload();
                        }
                    })
                    .catch((e) => {
                        this.$notify({
                            title: 'Error',
                            type: 'error',
                            message: e.message,
                            duration: 2000
                        });
                    });
            }
        },
    }
</script>

<style scoped lang="scss">
    .watchlist-button {
        font-size: 12px !important;

        @media screen and (min-width: 400px) {
            &__desktop {
                display: inline-block;
            }
            &.hidden {
                display: none;
            }
        }
        @media screen and (max-width: 400px) {
            &__desktop {
                display: none;
            }
            &.hidden {
                display: inline-block;
            }
        }
    }
</style>
