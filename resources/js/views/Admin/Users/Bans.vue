<template>
    <default-layout>
        <div class="container" v-if="!$apolloData.loading && user">
            <h3><router-link :to="{name: 'admin.users.show', params: {id: user.id}}">{{user.name_full}}</router-link>'s Ban History</h3>
            <table class="table table-striped">
                <caption class="d-none">Table displaying the user's bans</caption>
                <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Reason</th>
                    <th scope="col">Type</th>
                    <th scope="col">Status</th>
                    <th scope="col">Banned By</th>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                <tr v-for="ban in user.bans">
                    <td>{{ban.id}}</td>
                    <td>{{ban.reason.name}}</td>
                    <td>
                        <template v-if="ban.is_local">
                            🏠 Local Ban
                        </template>
                        <template v-else>
                            🌍 Network Ban
                        </template>
                    </td>
                    <td>
                        <template v-if="ban.is_active">
                            ⏳ Active
                        </template>
                        <template v-else-if="ban.repealed_at">
                            Repealed
                        </template>
                        <template v-else>
                            Expired
                        </template>
                    </td>
                    <td>
                        <router-link :to="{name: 'admin.users.show', params: {id: ban.banner.id}}">{{ban.banner.name_full}}</router-link>
                    </td>
                    <td>
                        <template v-if="ban.is_active">
                            Ends:
                            <template v-if="ban.ends_at">
                                {{ban.ends_at | dateTimeFormat('D/M/Y H:m')}}
                            </template>
                            <template v-else>
                                Indefinite
                            </template>
                        </template>
                        <template v-else-if="ban.repealed_at">
                            Repealed: {{ban.repealed_at | dateTimeFormat('D/M/Y H:m')}}
                        </template>
                        <template v-else>
                            Ended: {{ban.ends_at | dateTimeFormat('D/M/Y H:m')}}
                        </template>
                    </td>
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
        name: "Bans",
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
                        bans {
                            id
                            body
                            starts_at
                            ends_at
                            repealed_at

                            is_active
                            is_local

                            reason {
                                name
                                body
                            }
                            banner {
                                id
                                name_full
                            }
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
