<template>
    <default-layout>
        <div class="container" v-if="!$apolloData.loading && user">
            <h3><router-link :to="{name: 'admin.users.show', params: {id: user.id}}">{{user.name_full}}</router-link>'s Roles</h3>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="role in user.roles">
                    <td>{{role.id}}</td>
                    <td>{{role.name}}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </default-layout>
</template>

<script>
    import DefaultLayout from "../../../components/layout/DefaultLayout";
    import gql from 'graphql-tag'

    export default {
        name: "Roles",
        components: {DefaultLayout},
        data() {
            return {
                user: null
            }
        },
        apollo: {
            user: {
                query: gql`query($id: ID!) {
                    userByID(id: $id) {
                        id
                        name_full
                        roles {
                            id
                            name
                        }
                    }
                }`,
                variables() {
                    return {
                        id: this.$route.params.id
                    }
                },
                result() {
                    if (!this.user) {
                        this.errors.record(['This user does not exist!'])
                    }
                },
                update: data => data.userByID
            }
        }
    }
</script>
