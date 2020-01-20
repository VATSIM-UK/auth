<template>
    <default-layout>
        <template v-slot:toolbar-right>
            <li class="nav-item" v-if="role">
                <mutation-button v-if="role" custom-classes="btn-warning"
                                 :mutation-query="isCreate ? createMutationQuery : updateMutationQuery"
                                 :variables="getUpdateParameters"
                                 :loadingText="isCreate ? 'Creating...' : 'Updating...'"
                                 :disabled="deleted || !role.name"
                                 @loading="success = null; errors.clear()"
                                 @done="onDone"
                                 @error="onError"><span class="fa fa-save"></span> {{isCreate ? 'Create' :
                    'Update'}}
                </mutation-button>
                <mutation-button v-if="!isCreate && hasPermissionTo('auth.roles.delete')" custom-classes="btn-danger"
                                 :mutation-query="deleteMutationQuery"
                                 :variables="{id: getID}"
                                 :require-confirmation="true"
                                 loadingText="Deleting"
                                 @loading="success = null; errors.clear(); deleted = true"
                                 @done="onDeleted"
                                 @error="onError"><span class="fa fa-trash-alt"></span> Delete
                </mutation-button>
            </li>
        </template>
        <template v-slot:toolbar-left>
            <li class="nav-item" v-if="editingPermissions">
                <button class="btn btn-info" @click="editingPermissions = false">
                    <span class="fa fa-arrow-left"></span> Done
                </button>
            </li>
        </template>

        <template v-slot:above-box>
            <error-message v-if="errors.any()" :errors="errors"></error-message>
            <success-message v-if="success" :hideAfter="5">{{success}}</success-message>
        </template>
        <div v-if="!$apolloData.loading && role">
            <template v-if="!editingPermissions">
                <editable-text dusk="role-name-input" v-if="!isCreate" class="h1" name="Role Name"
                               v-model.trim="role.name"
                               :min-length="2"></editable-text>
                <text-input dusk="role-name-input" v-else name="name" v-model.trim="role.name"
                            placeholder="Role Name"></text-input>

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
                        <td>{{assignedPermissions.length}}
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
                        <input type="number" class="form-control" name="password_refresh_rate"
                               v-model="role.password_refresh_rate"
                               placeholder="No Password Refresh Requirement">
                        <small id="passwordHelpBlock" class="form-text text-muted">
                            Leave blank for no requirement
                        </small>
                    </div>
                </div>

            </template>
            <template v-else>
                <permissions-assignment-matrix :assigned-permissions="assignedPermissions"
                                               :available-permissions="allPermissions"
                                               @permissionAdded="permissionAdded"
                                               @permissionRemoved="permissionRemoved"></permissions-assignment-matrix>
            </template>
        </div>
    </default-layout>
</template>
<script>
    import gql from 'graphql-tag'
    import PermissionsAssignmentMatrix from "../components/PermissionsAssignmentMatrix";
    import MutationButton from "../components/MutationButton";
    import Errors from '../components/ui/errors'
    import ErrorMessage from "../components/ui/ErrorMessage";
    import DefaultLayout from "../components/layout/DefaultLayout";

    export default {
        components: {DefaultLayout, MutationButton, PermissionsAssignmentMatrix, ErrorMessage},
        data() {
            return {
                errors: new Errors(),
                success: null,
                role: {
                    name: null,
                    number_users: 0,
                    require_password: false,
                    password_refresh_rate: null,
                },
                deleted: false,
                editingPermissions: false,
                allPermissions: {},
                assignedPermissions: [],
                isCreate: !this.$route.params.hasOwnProperty("id"),
                updateMutationQuery: gql`
                    mutation RoleEdit ($id: ID!, $name: String!, $require_password: Boolean!, $password_refresh_rate: Int, $permissions: Mixed) {
                        editRole (id: $id, name: $name, require_password: $require_password, password_refresh_rate: $password_refresh_rate, permissions: $permissions)
                    }
                `,
                createMutationQuery: gql`
                    mutation RoleCreate ($name: String!, $require_password: Boolean!, $password_refresh_rate: Int, $permissions: Mixed) {
                        createRole (name: $name, require_password: $require_password, password_refresh_rate: $password_refresh_rate, permissions: $permissions)
                    }
                `,
                deleteMutationQuery: gql`
                    mutation RoleDelete ($id: ID!) {
                        deleteRole (id: $id) {
                            id
                        }
                    }
                `
            }
        },
        methods: {
            permissionAdded: function (permission) {
                if (this.assignedPermissions.includes(permission)) {
                    return
                }

                this.assignedPermissions.push(permission)
            },
            permissionRemoved: function (permission) {
                if (!this.assignedPermissions.includes(permission)) {
                    return
                }
                for (var i = 0; i < this.assignedPermissions.length; i++) {
                    if (this.assignedPermissions[i] === permission) {
                        this.assignedPermissions.splice(i, 1);
                    }
                }
            },
            onDone: function () {
                this.success = this.isCreate ? 'Role Created! Redirecting...' : 'Role Updated!';

                if (this.isCreate) {
                    setTimeout(function () {
                        this.$router.push({name: 'admin.roles', params: {'refetch': true}})
                    }.bind(this), 5000);
                    return
                }

                this.$apollo.queries.role.refetch()
            },
            onError: function (errors) {
                this.deleted = false;
                let {graphQLErrors} = errors;
                this.errors.record(graphQLErrors)
            },
            onDeleted: function () {
                this.success = 'Role Deleted! Redirecting...';
                this.deleted = true;

                setTimeout(function () {
                    this.$router.push({name: 'admin.roles', params: {'refetch': true}})
                }.bind(this), 5000);
            }
        },
        computed: {
            getID: function () {
                return this.$route.params.id;
            },
            getUpdateParameters: function () {
                return {
                    id: this.getID,
                    name: this.role.name,
                    require_password: this.role.require_password,
                    password_refresh_rate: this.role.password_refresh_rate,
                    permissions: this.assignedPermissions
                }
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
                        id: this.getID
                    }
                },
                result(result) {
                    if (this.role) {
                        this.assignedPermissions = this.role.permissions.map(assignment => assignment.permission)
                    } else {
                        this.errors.record(['This role does not exists!'])
                    }
                },
                error(errors) {
                    this.errors.record(errors);
                },
                skip() {
                    return this.isCreate
                },
            },
            allPermissions: {
                query: gql`{
                        permissions
                    }`,
                update: data => data.permissions
            }
        }
    }
</script>
