<template>
    <div v-if="!$apolloData.loading">
        <editable-text class="h1" name="Role Name" :value="role.name"></editable-text>
        <table class="table table-sm table-dark text-center">
            <thead>
            <tr>
                <th scope="col">Number of Assigned Users</th>
                <th scope="col">Number of Assigned Permissions</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{role.number_users}}</td>
                <td>{{role.permissions.length}} <button class="btn btn-sm btn-info">Edit Permissions</button></td>
            </tr>
            </tbody>
        </table>
        <hr>
        <div class="row">
            <div class="col">
                <label>Require Secondary Password:</label>
                <div>
                    <div class="btn-group btn-group-toggle">
                        <label class="btn btn-outline-dark" :class="{'btn-success': role.require_password}">
                            <input type="radio" name="require_password" :value="true" v-model="role.require_password"> Yes
                        </label>
                        <label class="btn btn-outline-dark" :class="{'btn-warning': !role.require_password}">
                            <input type="radio" name="require_password" :value="false" v-model="role.require_password"> No
                        </label>
                    </div>
                </div>
            </div>
            <div class="col">
                <label>Required Password Refresh Rate (Days):</label>
                <input type="number" class="form-control" v-model="role.password_refresh_rate" placeholder="No Password Refresh Requirement">
                <small id="passwordHelpBlock" class="form-text text-muted">
                    Leave blank for no requirement
                </small>
            </div>
        </div>
        <permissions-assignment-matrix :assigned-permissions="perms" :available-permissions="availPerms"></permissions-assignment-matrix>
    </div>
</template>
<script>
    import gql from 'graphql-tag'
    import Errors from '../components/ui/errors'
    import PermissionsAssignmentMatrix from "../components/PermissionsAssignmentMatrix";

    export default {
        components: {PermissionsAssignmentMatrix},
        data() {
            return {
                role: {},
                availPerms: {
                    "auth": {
                        "permissions": [
                            "assign"
                        ],
                        "roles": [
                            "create",
                            "update",
                            "delete",
                            "assign"
                        ]
                    }
                },
                perms: []
            }
        },
        methods: {},
        apollo: {
            role: {
                query: gql`query FetchRole($id: ID!){role(id: $id) {
                name
                number_users
                require_password
                password_refresh_rate
                permissions {
                    permission
                }
              }}`,
                variables () {
                    return {
                        id: this.$route.params.id
                    }
                },
            }
        }
    }
</script>
