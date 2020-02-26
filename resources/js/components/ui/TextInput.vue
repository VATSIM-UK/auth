<template>
  <div class="input-group">
    <input class="form-control"
           :class="{'is-invalid': errors.has(name)}"
           :type="type"
           :value="value"
           :placeholder="placeholder"
           @input="updateInput">

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
        methods: {
            updateInput(event) {
                this.errors.clear(this.name);
                this.$emit('input', event.target.value)
            }
        }
    }
</script>
