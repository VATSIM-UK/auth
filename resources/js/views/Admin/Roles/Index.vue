<template>
    <default-layout>
        <template v-slot:toolbar-right>
            <router-link :to="{ name: 'admin.role.create'}"
                         class="btn btn-sm btn-info">
                <span class="fa fa-plus"></span> New
            </router-link>
        </template>
        <table class="table" aria-describedby="A table showing the available roles">
            <thead class="thead-dark">
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Requires User Have A Password</th>
                <th scope="col">Required Password Refresh Rate (Days)</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="role in roles">
                <th scope="row">{{role.name}}</th>
                <td>{{role.require_password ? "Yes" : "No"}}</td>
                <td>{{role.password_refresh_rate}}</td>
                <td>
                    <router-link :to="{ name: 'admin.role.update', params: { id: role.id } }"
                                 class="btn btn-sm btn-info">Alter Role
                    </router-link>
                </td>
            </tr>
            </tbody>
        </table>
    </default-layout>
</template>
<script>
    import gql from 'graphql-tag'
    import Errors from '../../../components/ui/errors'
    import DefaultLayout from "../../../components/layout/DefaultLayout";

    export default {
        components: {DefaultLayout},
        data() {
            return {
                roles: [],
                errors: new Errors(),
                success: null
            }
        },
        mounted() {
            if (this.$route.params.refetch) {
                this.$apollo.queries.roles.refetch()
            }
        },
        apollo: {
            roles: gql`{roles {
                id
                name
                require_password
                password_refresh_rate
              }}`,
        }
    }
</script>
