<template>
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">{{title}}</h4>
        <template v-if="isErrorsObject">
            <ul v-if="errors.count() > 1 || !Array.isArray(errors.all())">
                <template v-for="error in errors.errors">
                    <li v-if="!Array.isArray(error)">{{error}}</li>
                    <li v-for="message in error">{{message}}</li>
                </template>
            </ul>
            <span v-else>{{errors.first()}}</span>
        </template>

        <slot></slot>
    </div>
</template>

<script>
    import Errors from "./errors"

    export default {
        name: "ErrorMessage",
        props: {
            errors: {
                type: Errors
            },
            title: {
                type: String,
                default: 'Whoops! There was a problem'
            }
        },
        computed: {
            isErrorsObject: function () {
                return this.errors && this.errors instanceof Errors
            }
        }
    }
</script>
