<template>
    <span>
        <span v-if="!editing" :class="['inline-edit', 'd-inline-block']" @click="beginEdit">
            <span v-if="textValue">
                {{textValue}}
            </span>
            <span v-else>
                <i>{{name}}</i>
            </span>
        </span>
        <div v-else class="input-group">
            <input type="text" v-model="editingValue" class="form-control" placeholder="name" aria-label="name">
             <div class="input-group-append">
                <button class="btn btn-success" @click="endEdit"><span class="fa fa-check"></span></button>
                <button class="btn btn-warning" @click="cancelEdit">
                    <span class="fa fa-trash-alt"></span>
                </button>
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
                this.$emit('changed', this.textValue)
            },
        }
    }
</script>
<style scoped>
    .input-group {
        padding: 5px;
    }
</style>
