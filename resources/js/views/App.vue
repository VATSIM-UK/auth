<template>
    <router-view v-if="!$apollo.queries.authUser.loading"></router-view>
    <loading-cover v-else></loading-cover>
</template>

<script>
    import gql from 'graphql-tag'
    import LoadingCover from "../components/ui/LoadingCover";

    export default {
        components: {LoadingCover},
        apollo: {
            authUser: {
                query: gql`{authUser {
                    name_first
                    name_last
                    all_permissions
                }}`,
                result(result) {
                    this.$store.commit('initAuthUser', {
                        name_first: result.data.authUser.name_first,
                        name_last: result.data.authUser.name_last,
                        permissions: result.data.authUser.all_permissions
                    });
                }
            }
        },
    }
</script>
