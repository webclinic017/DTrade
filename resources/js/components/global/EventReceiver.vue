<template>
    <div>
        <multi-factor :user-id="userId"></multi-factor>
    </div>
</template>

<script>
    import MultiFactor from "./events/MultiFactor";

    export default {
        components: {
            MultiFactor
        },
        props: {
            'userId': {
                type: Number,
                required: true,
            }
        }
    }
</script>

<style scoped>

</style>
