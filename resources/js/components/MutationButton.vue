<template>
    <button class="btn" :class="{'btn-danger': error}" @click="performAction"
            :disabled="loading || disabled">
        <template v-if="!loading">
            <slot v-if="!error"></slot>
            <span v-else><span class="fa fa-times"></span> Error. Try Again?</span>
        </template>
        <span v-else>
            <div class="spinner-grow spinner-grow-sm" role="status">
            </div>
            {{loadingText}}
        </span>
    </button>
</template>

<script>
    export default {
        name: "MutationButton",
        props: {
            mutationQuery: {
                required: true
            },
            variables: {
                default: {}
            },
            loadingText: {
                default: 'Loading...'
            },
            disabled: {
                type: Boolean,
                default: false
            }
        },
        data: function () {
            return {
                loading: false,
                error: false
            }
        },
        methods: {
            performAction: function () {
                this.$emit('loading');
                this.error = false;
                this.loading = true;
                this.$apollo.mutate({
                    // Query
                    mutation: this.mutationQuery,
                    // Parameters
                    variables: this.variables,
                }).then((data) => {
                    this.loading = false;
                    this.$emit('done', data);
                }).catch((error) => {
                    this.error = true;
                    this.loading = false;
                    this.$emit('error', error);
                });
            }
        }
    }
</script>

<style scoped>

</style>
