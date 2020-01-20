<template>
    <div>
        <div class="card" style="width: 100%"
             v-for="(group, key) in availablePermissions">
            <div class="card-header">{{key}}</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <input type="checkbox"
                           :name="'permission:'+generateFullPermissionName(key, '*')"
                           :checked="hasPermission(generateFullPermissionName(key, '*'))"
                           :disabled="disabled"
                           @change="onPermissionChange($event, generateFullPermissionName(key, '*'))">
                    <strong>All in this category</strong> ({{generateFullPermissionName(key, '*')}})
                </li>
                <li class="list-group-item">
                    <input type="checkbox"
                           :name="'permission:'+generateFullPermissionName(key, null)"
                           :checked="hasPermission(generateFullPermissionName(key, null))"
                           :disabled="hasPermission(generateFullPermissionName(key, '*')) || disabled"
                           @change="onPermissionChange($event, generateFullPermissionName(key, null))">
                    <strong>Index this category</strong> ({{generateFullPermissionName(key, null)}})
                </li>
                <li class="list-group-item" v-if="!isArray(group)">
                    <div class="row no-gutters">
                        <permissions-assignment-matrix v-for="(innerGroup, innerGroupKey, index) in group"
                                                       :key="index"
                                                       :class="{'col-12':!isBaseLevel, 'col-6':isBaseLevel}"
                                                       :path-prefix="generatePassthroughPrefix(key)"
                                                       :assigned-permissions="assignedPermissions"
                                                       :available-permissions="createObjectFromParts(innerGroup, innerGroupKey)"
                                                       :disabled="hasPermission(generateFullPermissionName(key, '*'))"
                                                       @permissionAdded="permissionAdded"
                                                       @permissionRemoved="permissionRemoved"></permissions-assignment-matrix>
                    </div>
                </li>
                <li class="list-group-item" v-else v-for="permission in group">
                    <input type="checkbox"
                           :name="'permission:'+generateFullPermissionName(key, permission)"
                           :checked="hasPermission(generateFullPermissionName(key, permission))"
                           :disabled="hasPermission(generateFullPermissionName(key, '*')) || disabled"
                           @change="onPermissionChange($event, generateFullPermissionName(key, permission))">
                    {{permission}} ({{generateFullPermissionName(key, permission)}})
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        name: "PermissionsAssignmentMatrix",
        props: {
            assignedPermissions: {
                required: true,
                type: Array,
            },
            availablePermissions: {
                required: true,
            },
            pathPrefix: {
                type: String
            },
            disabled: {
                type: Boolean
            }
        },
        methods: {
            isArray: function (a) {
                return (!!a) && (a.constructor === Array);
            },
            generateFullPermissionName: function (groupKey, base) {
                var permission = '';

                if (this.pathPrefix) {
                    permission = this.pathPrefix + '.' + groupKey;
                } else {
                    permission = groupKey;
                }

                if (base) {
                    permission = permission + '.' + base;
                }
                return permission;
            },
            generatePassthroughPrefix: function (groupKey) {
                return this.pathPrefix ? this.pathPrefix + '.' + groupKey : groupKey;
            },
            onPermissionChange(event, permission) {
                if (event.target.checked) {
                    return this.permissionAdded(permission)
                }
                return this.permissionRemoved(permission)
            },
            permissionAdded: function (permission) {
                this.$emit('permissionAdded', permission)
            },
            permissionRemoved: function (permission) {
                this.$emit('permissionRemoved', permission)
            },
            hasPermission: function (permission) {
                return this.assignedPermissions.includes(permission);
            },
            createObjectFromParts(value, key) {
                var obj = {};
                obj[key] = value;
                return obj;
            }
        },
        computed: {
            isBaseLevel: function () {
                return !this.pathPrefix
            }
        }
    }
</script>
