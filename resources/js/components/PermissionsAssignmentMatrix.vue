<template>
    <div>
        <div v-for="(group, key) in groupedPermissions" class="ml-1">
            <h3>{{key}} (* or single)</h3>
            <permissions-assignment-matrix v-if="!isArray(group)" :path-prefix="generatePassthroughPrefix(key)"
                                           :assigned-permissions="permissionsHas"
                                           :available-permissions="group"></permissions-assignment-matrix>
            <ul v-else>
                <li v-for="permission in group">{{permission}} ({{generateFullPermissionName(key, permission)}})</li>
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
            }
        },
        data() {
            return {
                permissionsHas: this.assignedPermissions,
                groupedPermissions: this.availablePermissions,
                depth: null,
                prefix: this.pathPrefix,
            }
        },
        methods: {
            isArray: function (a) {
                return (!!a) && (a.constructor === Array);
            },
            generateFullPermissionName: function (groupKey, base) {
                return this.prefix + '.' + groupKey + '.' + base;
            },
            generatePassthroughPrefix: function (groupKey){
                return  this.prefix ? this.prefix + '.' + groupKey : groupKey;
            }
        }
    }
</script>

<style scoped>

</style>
