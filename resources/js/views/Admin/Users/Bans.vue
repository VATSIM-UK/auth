<template>
    <default-layout>
        <div class="container" v-if="!$apolloData.loading && user">
            <h3><router-link :to="{name: 'admin.users.show', params: {id: user.id}}">{{user.name_full}}</router-link>'s Ban History</h3>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Reason</th>
                    <th>Type</th>
                    <th>Active</th>
                    <th>Banned By</th>
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
                        <template v-else>
                            ✅ Expired
                        </template>
                    </td>
                    <td>
                        {{ban.banner.name_full}} ({{ban.banner.id}})
                    </td>
                    <td>
                        <template v-if="ban.is_active">
                            Ends:
                            <template v-if="ban.ends_at">
                                {{ban.ends_at | dateTimeFormat('d/m/Y H:m')}}
                            </template>
                            <template v-else>
                                Indefinite
                            </template>
                        </template>
                        <template v-else>
                            Ended: {{ban.ends_at | dateTimeFormat('d/m/Y H:m')}}
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
