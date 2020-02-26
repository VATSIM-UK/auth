<template>
  <transition name="fade" @after-leave="$emit('done')">
    <div class="alert alert-success" v-show="countdown || !hideAfter" role="alert">
      <slot/>
      <div v-if="hideAfter" style="height: 2px" class="progress mt-2">
        <div class="progress-bar bg-success"
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
    export default {
        name: "SuccessMessage",
        props: {
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
                this.timer = setInterval(this.successProgress, 100)
            }
        },
        methods: {
            successProgress: function () {
                this.countdown = this.countdown - 0.1;
                if (this.countdown <= 0) {
                    clearInterval(this.timer);
                    this.countdown = null;
                    this.timer = null;
                }
            }
        },
        computed: {
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
</style>
