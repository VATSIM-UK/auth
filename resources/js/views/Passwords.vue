<template>
    <div>
        <success-message :message="success" v-if="success"></success-message>
        <div v-if="authUser.has_password">
            You currently have a secondary password set. You may change it below:
            <text-input name="old_password" :errors="errors" v-model="old_password"
                        placeholder="Old Password"></text-input>
            <text-input name="new_password" :errors="errors" v-model="new_password"
                        placeholder="New Password"></text-input>
            <button class="btn btn-info" @click="updatePassword(false)" :disabled="!old_password || !new_password">
                Update
            </button>
            <small class="form-text text-muted">Passwords must be at least 8 characters long, containing a uppercase and
                a lowercase letter, as well as a number.</small>

            <p>You may also remove your secondary password completely:</p>
            <text-input name="current_password" :errors="errors" v-model="current_password"
                        placeholder="Current Password"></text-input>
            <button class="btn btn-info" @click="removePassword()" :disabled="!current_password">Update</button>
        </div>
        <div v-else>
            You do not currently have a secondary password set. Add one below:
            <text-input name="new_password" :errors="errors" v-model="new_password" placeholder="Password"></text-input>
            <text-input name="new_confirm" v-model="new_password_confirm" placeholder="Password (again)"></text-input>
            <button class="btn btn-info" @click="updatePassword(true)"
                    :disabled="(new_password !== new_password_confirm) || !new_password">Add Password
            </button>
            <small class="form-text text-muted">Passwords must be at least 8 characters long, containing a uppercase and
                a lowercase letter, as well as a number.</small>
        </div>
    </div>
</template>
<script>
    import gql from 'graphql-tag'
    import Errors from '../components/ui/errors'

    export default {
        data() {
            return {
                authUser: {},
                old_password: null,
                new_password: null,
                new_password_confirm: null,
                current_password: null,
                errors: new Errors(),
                success: null
            }
        },
        methods: {
            removePassword() {
                this.$apollo.mutate({
                    // Query
                    mutation: gql`mutation ($current_password: String!) {
                    removePassword(current_password: $current_password)
                  }`,
                    // Parameters
                    variables: {
                        current_password: this.current_password,
                    },
                }).then((data) => {
                    // Result
                    this.authUser.has_password = false;
                    this.new_password = this.old_password = null;
                    this.success = "Secondary Password Removed!";
                }).catch(error => this.recordErrors(error))
            },
            updatePassword(isNew) {
                this.errors.clear();
                this.$apollo.mutate({
                    // Query
                    mutation: gql`mutation ($old_password: String, $new_password: String!) {
                    updatePassword(old_password: $old_password, new_password: $new_password)
                  }`,
                    // Parameters
                    variables: {
                        old_password: isNew ? null : this.old_password,
                        new_password: this.new_password,
                    },
                }).then((data) => {
                    // Result
                    this.authUser.has_password = true;
                    this.new_password = this.new_password_confirm = null;
                    this.success = "Secondary Password Set!";
                }).catch(error => this.recordErrors(error))
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
        }
    }
</script>
