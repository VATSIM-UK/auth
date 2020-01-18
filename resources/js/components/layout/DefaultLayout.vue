<template>
    <div>
        <div class="container pb-2" v-if="$slots['toolbar-left'] || $slots['toolbar-right']">
            <nav class="navbar navbar-dark bg-dark">
                <ul class="navbar-nav mr-auto">
                    <slot name="toolbar-left"/>
                </ul>

                <ul class="navbar-nav ml-auto">
                    <slot name="toolbar-right"/>
                </ul>
            </nav>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <error-message v-if="error" :title="error" :hideAfter="10" @done="error = null"></error-message>
                <slot name="above-box"/>
                <div class="card">
                    <div class="card-header">
                        <router-link :to="{ name: 'dashboard' }" v-if="this.$router.currentRoute.name != 'dashboard'">
                            Back
                            to Dashboard
                        </router-link>

                        <div class="float-right">Hi, {{$store.state.authUser.name_first}}</div>
                    </div>

                    <div class="card-body" style="position:relative">
                        <loading-cover v-if="isLoadingData"></loading-cover>
                        <slot v-else></slot>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import LoadingCover from "../ui/LoadingCover";
    import ErrorMessage from "../ui/ErrorMessage";


    export default {
        name: "DefaultLayout",
        data() {
            return {
                authUser: {},
                error: null,
            }
        },
        methods: {
            routePermissionCheck: function (route, next) {
                this.permissionError = false;
                if (!route.meta.hasOwnProperty('permission')) {
                    next()
                } else {
                    let permission = route.meta.permission;
                    if (!this.hasPermissionTo(permission)) {
                        this.error = "You don't have permission to visit that page!";
                        next(false)
                    } else {
                        next()
                    }
                }
            }
        },
        computed: {
            isLoadingData: function () {
                return (this.$apollo.loading || this.$root.dataIsLoading) && this.shouldEnableLoadingCover
            },
            shouldEnableLoadingCover: function () {
                return typeof this.$route.meta.globalLoadState == "undefined" || this.$route.meta.globalLoadState != false
            },
            hasHistory: function () {
                return window.history.length > 0;
            }
        },
        mounted() {
            this.routePermissionCheck(this.$route, function (passed) {
                if (passed === false) {
                    this.$router.replace({name: 'dashboard'});
                }
            }.bind(this));
            this.$router.beforeEach((to, from, next) => this.routePermissionCheck(to, next))
        },
        components: {
            ErrorMessage,
            LoadingCover
        }
    }
</script>

<style scoped>

</style>
