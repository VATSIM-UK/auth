<template>
  <transition name="fade" @after-leave="$emit('done')">
    <div class="alert alert-danger" v-show="countdown || !hideAfter" role="alert">
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

      <slot/>

      <div v-if="hideAfter" style="height: 2px" class="progress mt-2">
        <div class="progress-bar bg-danger"
             role="progressbar"
             aria-valuemin="0"
             :aria-valuenow="hideAfter - countdown"
             :aria-valuemax="hideAfter"
             :style="{ width: progressBarWidth }">
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
    import Errors from "./errors"

    export default {
        name: "ErrorMessage",
        props: {
            errors: {
                type: Errors,
                required: true
            },
            title: {
                type: String,
                default: 'Whoops! There was a problem'
            },
            hideAfter: {
                type: Number,
                default: null
            }
        },
        data() {
            return {
                countdown: null,
                timer: null
            }
        },
        mounted() {
            if (this.hideAfter) {
                this.countdown = this.hideAfter;
                this.timer = setInterval(this.errorProgress, 100)
            }
        },
        methods: {
            errorProgress: function () {
                this.countdown = this.countdown - 0.1;
                if (this.countdown <= 0) {
                    clearInterval(this.timer);
                    this.countdown = null;
                    this.timer = null;
                }
            }
        },
        computed: {
            isErrorsObject: function () {
                return this.errors && this.errors instanceof Errors
            },
            progressBarWidth: function () {
                return ((this.hideAfter - this.countdown) * 100 / this.hideAfter) + '%';
            }
        }
    }
</script>

<style scoped>
  .fade-leave-active {
    transition: opacity .5s;
  }

  .fade-leave-to {
    opacity: 0;
  }

  ul {
    margin-bottom: 0;
  }
</style>
