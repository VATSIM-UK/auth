<template>
    <span class="editable-text">
        <span v-if="!editing" :class="['inline-edit', 'd-inline-block']" @click="beginEdit">
            <span v-if="textValue">
                {{textValue}}
            </span>
        </span>
        <div v-else class="input-group">
            <input type="text"
                   v-model="editingValue"
                   class="form-control"
                   :class="{'is-invalid': !meetsLengthRequirement}"
                   :placeholder="name"
                   :aria-label="name">
             <div class="input-group-append">
                <button class="btn btn-success" @click="endEdit"
                        :disabled="!meetsLengthRequirement"><span
                    class="fa fa-check"></span></button>
                <button class="btn btn-warning" @click="cancelEdit">
                    <span class="fa fa-trash-alt"></span>
                </button>
            </div>
            <div v-if="!meetsLengthRequirement" class="invalid-tooltip">
                The {{name.toLowerCase()}} must be at least {{minLength}} characters long
            </div>
        </div>
    </span>
</template>

<script>
    export default {
        name: "EditableText",
        props: {
            value: {
                required: true
            },
            name: {
                type: String,
                required: true
            },
            minLength: {
                type: Number
            }
        },
        data: function () {
            return {
                textValue: this.value,
                editingValue: this.textValue,
                editing: false
            }
        },
        methods: {
            beginEdit: function () {
                this.editingValue = this.textValue;
                this.editing = true;
            },
            cancelEdit: function () {
                this.editingValue = this.textValue;
                this.editing = false;
            },
            endEdit: function () {
                this.textValue = this.editingValue;
                this.editing = false;
                this.$emit('input', this.textValue)
            },
        },
        computed: {
            meetsLengthRequirement: function () {
                return this.minLength && this.editingValue.length >= this.minLength
            }
        }
    }
</script>
<style scoped>
    .input-group {
        padding: 5px;
    }
</style>
