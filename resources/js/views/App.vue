<template>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <router-link :to="{ name: 'dashboard' }" v-if="this.$router.currentRoute.name != 'dashboard'"> Back
                        to Dashboard
                    </router-link>

                    <div class="float-right">Hi, {{authUser.name_first}}</div>
                </div>

                <div class="card-body" style="position:relative">
                    <loading-cover v-if="isLoadingData"></loading-cover>
                    <router-view></router-view>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import gql from 'graphql-tag'
    import LoadingCover from "./ui/LoadingCover";

    var watcher;

    export default {
        data() {
            return {
                authUser: {}
            }
        },
        apollo: {
            authUser: gql`{authUser {
                name_first
                name_last
              }}`
        },
        computed: {
            isLoadingData: function () {
                return (this.$apollo.loading || this.$root.dataIsLoading) && this.shouldEnableLoadingCover
            },
            shouldEnableLoadingCover: function () {
                return typeof this.$route.meta.globalLoadState == "undefined" || this.$route.meta.globalLoadState != false
            }
        },
        components: {
            LoadingCover
        }
    }
</script>
