<template>
    <div>
        <error-message v-if="errors.any()" :errors="errors"></error-message>
        <success-message v-if="success">{{success}}</success-message>
        <div v-if="!$apolloData.loading && role">
            <template v-if="!editingPermissions">

                <editable-text class="h1" name="Role Name" v-model.trim="role.name" :min-length="2"></editable-text>

                <mutation-button class="btn-warning" :mutation-query="updateMutationQuery"
                                 :variables="getUpdateParameters()"
                                 loadingText="Updating..."
                                 @loading="success = null; errors.clear()"
                                 @done="success = 'Role Updated!'; $apollo.queries.role.refetch()"
                                 @error="onUpdateError"><span class="fa fa-save"></span> Update
                </mutation-button>

                <table class="table table-sm table-dark text-center mt-1"
                       aria-describedby="Table showing basic statistics for the role">
                    <thead>
                    <tr>
                        <th scope="col">Number of Assigned Users</th>
                        <th scope="col">Number of Assigned Permissions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{role.number_users}}</td>
                        <td>{{role.permissions.length}}
                            <button class="btn btn-sm btn-info" @click="editingPermissions = true">Edit Permissions
                            </button>
                        </td>
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
                                    <input type="radio" name="require_password" :value="true"
                                           v-model="role.require_password">
                                    Yes
                                </label>
                                <label class="btn btn-outline-dark" :class="{'btn-warning': !role.require_password}">
                                    <input type="radio" name="require_password" :value="false"
                                           v-model="role.require_password">
                                    No
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <label>Required Password Refresh Rate (Days):</label>
                        <input type="number" class="form-control" v-model="role.password_refresh_rate"
                               placeholder="No Password Refresh Requirement">
                        <small id="passwordHelpBlock" class="form-text text-muted">
                            Leave blank for no requirement
                        </small>
                    </div>
                </div>

            </template>
            <template v-else>
                <button class="btn btn-info" @click="editingPermissions = false">
                    <span class="fa fa-arrow-left"></span> Done
                </button>
                <permissions-assignment-matrix :assigned-permissions="perms" :available-permissions="availPerms"
                                               @permissionAdded="permissionAdded"
                                               @permissionRemoved="permissionRemoved"></permissions-assignment-matrix>
            </template>
        </div>
    </div>
</template>
<script>
    import gql from 'graphql-tag'
    import PermissionsAssignmentMatrix from "../components/PermissionsAssignmentMatrix";
    import MutationButton from "../components/MutationButton";
    import Errors from '../components/ui/errors'
    import ErrorMessage from "../components/ui/ErrorMessage";

    export default {
        components: {MutationButton, PermissionsAssignmentMatrix, ErrorMessage},
        data() {
            return {
                errors: new Errors(),
                success: null,
                role: null,
                editingPermissions: false,
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
                perms: [],
                updateMutationQuery: gql`mutation RoleEdit ($id: ID!, $name: String!, $require_password: Boolean!, $password_refresh_rate: Int, $permissions: Mixed) {
                editRole (id: $id, name: $name, require_password: $require_password, password_refresh_rate: $password_refresh_rate, permissions: $permissions)
                }
                `
            }
        },
        methods: {
            permissionAdded: function (permission) {
                if (this.perms.includes(permission)) {
                    return
                }

                this.perms.push(permission)
            },
            permissionRemoved: function (permission) {
                if (!this.perms.includes(permission)) {
                    return
                }
                for (var i = 0; i < this.perms.length; i++) {
                    if (this.perms[i] === permission) {
                        this.perms.splice(i, 1);
                    }
                }
            },
            getUpdateParameters: function () {
                return {
                    id: this.$route.params.id,
                    name: this.role.name,
                    require_password: this.role.require_password,
                    password_refresh_rate: this.role.password_refresh_rate,
                    permissions: this.perms
                }
            },
            onUpdateError: function (errors) {
                let {graphQLErrors} = errors;
                this.errors.record(graphQLErrors)
            }
        },
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
                variables() {
                    return {
                        id: this.$route.params.id
                    }
                },
                result(result) {
                    if (this.role) {
                        this.perms = this.role.permissions.map(assignment => assignment.permission)
                    } else {
                        this.errors.record(['This role does not exists!'])
                    }
                },
                error(errors) {
                    this.errors.record(errors);
                }
            },
        }
    }
</script>
