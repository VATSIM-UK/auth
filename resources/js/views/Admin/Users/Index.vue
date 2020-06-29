<template>
    <default-layout>
        <template v-slot:above-box>
            <error-message v-if="error" :show-progress="false" :title="error" :hideAfter="10"
                           @done="error = null"></error-message>
        </template>

        <div class="container">

            <h3>Search for a user</h3>
            <div class="row">
                <div class="col-8">
                    <input type="text" class="form-control" placeholder="CID" v-model.number="searchCID"/>
                </div>
                <div class="col">
                    <button class="btn btn-info" :disabled="!searchCID" @click="searchByCID">Search</button>
                </div>
            </div>
        </div>
    </default-layout>
</template>

<script>
    import DefaultLayout from "../../../components/layout/DefaultLayout";
    import gql from 'graphql-tag'
    import ErrorMessage from "../../../components/ui/ErrorMessage";

    export default {
        name: "Users",
        components: {ErrorMessage, DefaultLayout},
        data() {
            return {
                searchCID: null,
                error: null,
            }
        },
        methods: {
            searchByCID() {
                this.$apollo.query({
                    query: gql`query ($id: ID!) {
                        userByID(id: $id) {
                            id
                        }
                      }`,
                    variables: {
                        id: this.searchCID
                    }
                }).then((result) => {
                    if (!result.data.userByID) {
                        this.error = "The requested user was not found!";
                        return;
                    }

                    this.$router.push({name: "admin.users.view", params: {id: this.searchCID}})
                })
            }
        }
    }
</script>
