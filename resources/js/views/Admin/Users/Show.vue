<template>
    <default-layout>
        <template v-slot:above-box>
            <error-message v-if="errors.any()" :errors="errors"></error-message>
        </template>

        <template v-if="!$apolloData.loading && user">
            <div class="row">
                <div class="col">
                    <h3>{{user.name_full}}</h3>
                </div>
                <div class="col">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <template v-if="user.banned">
                                🚨 Currently banned
                            </template>
                            <template v-else>
                                ✅ Currently in good standing
                            </template>
                        </li>
                        <li class="list-group-item">
                            <b>Primary Membership</b>
                            <div v-if="user.is_home_member">
                                🏠 Home Member
                            </div>
                            <div v-else>
                                🌍 {{user.primaryMembership.name}} Member ({{user.primaryMembership.pivot.division}} - {{user.primaryMembership.pivot.region}})
                            </div>
                        </li>
                        <li class="list-group-item" v-if="user.secondaryMemberships.length">
                            <b>Secondary Memberships</b>
                            <div v-for="membership in user.secondaryMemberships">
                                <template v-if="membership.identifier === 'TFR'">🚚</template>
                                <template v-else-if="membership.identifier === 'VST'">✈</template>
                                <template v-else>⚠</template>
                                Is {{membership.name}}
                            </div>
                        </li>
                        <li class="list-group-item">📅 Member of VATSIM since {{user.joined_at | dateTimeFormat("D/M/YYYY")}}</li>
                    </ul>
                </div>
            </div>
        </template>
    </default-layout>
</template>

<script>
    import DefaultLayout from "../../../components/layout/DefaultLayout";
    import gql from 'graphql-tag'
    import ErrorMessage from "../../../components/ui/ErrorMessage";
    import Errors from "../../../components/ui/errors";

    export default {
        name: "Users",
        components: {ErrorMessage, DefaultLayout},
        data() {
            return {
                user: null,
                errors: new Errors()
            }
        },
        apollo: {
            user: {
                query: gql`query($id: ID!) {
                    userByID(id: $id) {
                        id
                        name_first
                        name_last
                        name_full
                        slack_id
                        nickname
                        email
                        password_set_at
                        last_login
                        last_login_ip
                        remember_token
                        joined_at
                        cert_checked_at
                        created_at
                        is_home_member
                        is_transferring
                        is_visiting
                        primaryMembership {
                            name
                            pivot {
                                division
                                region
                            }
                        }
                        secondaryMemberships {
                            identifier
                            name
                        }
                        banned
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
