<template>
    <div>
        <table class="table">
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
                    <router-link :to="{ name: 'admin.role', params: { id: role.id } }" class="btn btn-sm btn-info">Alter Role</router-link>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
    import gql from 'graphql-tag'
    import Errors from '../components/ui/errors'

    export default {
        data() {
            return {
                roles: [],
                errors: new Errors(),
                success: null
            }
        },
        methods: {},
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
