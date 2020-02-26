<template>
    <default-layout>
        <success-message v-if="success">{{success}}</success-message>
        <div v-if="authUser.has_password">
            You currently have a secondary password set. You may change it below:
            <text-input type="password" name="old_password" :errors="errors" v-model="old_password"
                        placeholder="Old Password"></text-input>
            <text-input type="password" name="new_password" :errors="errors" v-model="new_password"
                        placeholder="New Password"></text-input>

            <mutation-button custom-classes="btn-info" :mutation-query="mutations.updatePassword"
                             :variables="{
                                    old_password: old_password,
                                    new_password: new_password,
                                }"
                             :disabled="!old_password || !new_password"
                             @loading="errors.clear()"
                             @done="onPasswordUpdated"
                             @error="recordErrors"
            >Update
            </mutation-button>

            <small class="form-text text-muted">Passwords must be at least 8 characters long, containing a uppercase and
                a lowercase letter, as well as a number.</small>

            <p>You may also remove your secondary password completely:</p>
            <text-input type="password" name="current_password" :errors="errors" v-model="current_password"
                        placeholder="Current Password"></text-input>
            <mutation-button custom-classes="btn-info" :mutation-query="mutations.removePassword"
                             :variables="{
                                    current_password: current_password,
                                }"
                             :disabled="!current_password"
                             @loading="errors.clear()"
                             @done="onPasswordRemoved"
                             @error="recordErrors"
            >Remove
            </mutation-button>

        </div>
        <div v-else>
            You do not currently have a secondary password set. Add one below:
            <text-input type="password" name="new_password" :errors="errors" v-model="new_password"
                        placeholder="Password"></text-input>
            <text-input type="password" name="new_confirm" v-model="new_password_confirm"
                        placeholder="Password (again)"></text-input>

            <mutation-button custom-classes="btn-info" :mutation-query="mutations.updatePassword"
                             :variables="{
                                    old_password: null,
                                    new_password: new_password,
                                }"
                             :disabled="(new_password !== new_password_confirm) || !new_password"
                             @loading="errors.clear()"
                             @done="onPasswordUpdated"
                             @error="recordErrors"
            >Update
            </mutation-button>

            <small class="form-text text-muted">Passwords must be at least 8 characters long, containing a uppercase and
                a lowercase letter, as well as a number.</small>
        </div>
    </default-layout>
</template>
<script>
    import gql from 'graphql-tag'
    import Errors from '../components/ui/errors'
    import MutationButton from "../components/MutationButton";
    import DefaultLayout from "../components/layout/DefaultLayout";

    export default {
        components: {DefaultLayout, MutationButton},
        data() {
            return {
                authUser: {},
                old_password: null,
                new_password: null,
                new_password_confirm: null,
                current_password: null,
                errors: new Errors(),
                success: null,
                mutations: {
                    removePassword: gql`mutation ($current_password: String!) {
                    removePassword(current_password: $current_password)
                  }`,
                    updatePassword: gql`mutation ($old_password: String, $new_password: String!) {
                    updatePassword(old_password: $old_password, new_password: $new_password)
                  }`,
                }
            }
        },
        methods: {
            onPasswordRemoved() {
                this.authUser.has_password = false;
                this.current_password = null;
                this.success = "Secondary Password Removed!";
            },
            onPasswordUpdated() {
                this.authUser.has_password = true;
                this.new_password = this.new_password_confirm = this.old_password = null;
                this.success = "Secondary Password Set!";
            },
            recordErrors(errors) {
                let {graphQLErrors} = errors;
                this.errors.record(graphQLErrors)
            }
        },
        apollo: {
            authUser: gql`{authUser {
                has_password
              }}`,
        },
    }
</script>
