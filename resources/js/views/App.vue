<template>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <router-link :to="{ name: 'dashboard' }" v-if="this.$router.currentRoute.name != 'dashboard'"> Back to Dashboard</router-link>

                    <div class="float-right">Hi, {{authUser.name_first}}</div>
                </div>

                <div class="card-body">
                    <router-view></router-view>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import gql from 'graphql-tag'

    export default {
        data () {
            return {
                authUser: {},
            }
        },
        apollo: {
            authUser: gql`{authUser {
                name_first
                name_last
              }}`,
        }
    }
</script>
