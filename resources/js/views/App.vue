<template>
    <div class="row justify-content-center">
        <div class="col-md-10">
            <error-message v-if="error" :title="error" :hideAfter="10" @done="error = null"></error-message>
            <div class="card">
                <div class="card-header">
                    <router-link :to="{ name: 'dashboard' }" v-if="this.$router.currentRoute.name != 'dashboard'"> Back
                        to Dashboard
                    </router-link>

                    <div class="float-right">Hi, {{authUser.name_first}}</div>
                </div>

                <div class="card-body" style="position:relative">
                    <loading-cover v-if="isLoadingData"></loading-cover>
                    <router-view v-else></router-view>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import gql from 'graphql-tag'
    import LoadingCover from "./ui/LoadingCover";
    import ErrorMessage from "../components/ui/ErrorMessage";
    import PermissionMet from '../../../vendor/vatsimuk/auth-package/src/Js/permissionValidity'

    export default {
        data() {
            return {
                authUser: {},
                error: null,
                permissionError: false
            }
        },
        apollo: {
            authUser: {
                query: gql`{authUser {
                    name_first
                    name_last
                    all_permissions
                }}`,
                result(result) {
                    this.routePermissionCheck(this.$route, function (passed) {
                        if (passed === false) {
                            this.$router.replace({name: 'dashboard'});
                        }
                    }.bind(this));
                }
            }
        },
        methods: {
            routePermissionCheck: function (route, next) {
                this.permissionError = false;
                if (!route.meta.hasOwnProperty('permission') || !this.authUser.all_permissions) {
                    next()
                } else {
                    let permission = route.meta.permission;
                    if (!PermissionMet(permission, this.authUser.all_permissions)) {
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
            }
        },
        mounted() {
            this.$router.beforeEach((to, from, next) => this.routePermissionCheck(to, next))
        },
        components: {
            ErrorMessage,
            LoadingCover
        }
    }
</script>
