<template>
    <span>
        <button v-if="!confirming" v-bind="$attrs" class="btn" :class="[{'btn-danger': error}, customClasses]"
                @click="performAction"
                :disabled="loading || disabled">
            <template v-if="!loading">
                <slot v-if="!error"></slot>
                <span v-else><span class="fa fa-times"></span> Error. Try Again?</span>
            </template>
            <span v-else-if="confirming">
                Are you sure?
            </span>
            <span v-else>
                <div class="spinner-grow spinner-grow-sm" role="status">
                </div>
                {{loadingText}}
            </span>
        </button>
        <template v-else>
            <div class="bg-light d-inline-block p-2 rounded">
            Are you sure?
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-success" @click="performAction"><span
                    class="fa fa-check"></span></button>
                <button type="button" class="btn btn-danger" @click="confirming = false"><span
                    class="fa fa-times"></span></button>
            </div>
            </div>
        </template>
    </span>
</template>

<script>
    export default {
        name: "MutationButton",
        inheritAttrs: false,
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
            },
            requireConfirmation: {
                type: Boolean,
                default: false
            },
            customClasses: {
                required: false,
                default: ''
            }
        },
        data: function () {
            return {
                loading: false,
                confirming: false,
                error: false
            }
        },
        methods: {
            performAction: function () {
                if (this.requireConfirmation && !this.confirming) {
                    this.confirming = true;
                    return;
                }

                this.confirming = false;

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
