<template>
    <div class="loader d-flex justify-content-center">
        <div class="spinner-border text-secondary my-auto" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</template>

<script>
    export default {
        name: "LoadingCover"
    }
</script>

<style scoped>
    .loader {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.9);
        z-index: 2;
    }
</style>
