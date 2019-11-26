<template>
    <div class="input-group">
        <input :type="type" class="form-control" :class="{'is-invalid': errors.has(name)}"
               v-model="currentValue" :placeholder="placeholder" @input="updateInput()">
        <div v-if="errors.has(name)" class="invalid-tooltip">
            {{errors.get(name)}}
        </div>
    </div>
</template>

<script>
    import Errors from './errors'
    export default {
        name: "TextInput",
        props: {
            name: {
                type: String,
                required: true
            },
            type: {
                type: String,
                default: 'text'
            },
            errors: {
                type: Object,
                default: function () {
                    return new Errors()
                }
            },
            value: {
                type: String,
                default: null
            },
            placeholder: {
                type: String,
            },
        },
        data() {
            return {
                currentValue: null,
            }
        },
        updated() {
            this.currentValue = this.value;
        },
        methods: {
            updateInput(){
                this.errors.clear(this.name);
                this.$emit('input', this.currentValue)
            }
        }
    }
</script>

<style scoped>

</style>
