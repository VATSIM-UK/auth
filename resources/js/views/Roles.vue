<template>
    <div>
        <ul>
            <li v-for="role in roles">{{role.name}}</li>
        </ul>
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
                name
              }}`,
        }
    }
</script>
