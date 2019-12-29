<template>
    <div class="alert alert-success" role="alert">
        {{message}}
    </div>
</template>

<script>
    export default {
        name: "SuccessMessage",
        functional: true,
        props: ['message']
    }
</script>
