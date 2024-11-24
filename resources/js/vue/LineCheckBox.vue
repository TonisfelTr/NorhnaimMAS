<template>
    <div class="line-checkbox" @click="toggleCheckbox">
        <input
            type="checkbox"
            class="visually-hidden"
            v-model="isChecked"
            :name="name"
        >
        <div :class="['line-checkbox__switcher', { checked: isChecked }]">
            <div class="line-checkbox__check"></div>
        </div>
        <span :class="spanClass">{{ label }}</span>
    </div>
</template>

<script>
export default {
    name: 'LineCheckbox',
    props: {
        checked: Boolean,
        name: String,
        spanClass: String,
        label: String,
    },
    data() {
        return {
            isChecked: this.checked,
        };
    },
    methods: {
        toggleCheckbox() {
            this.isChecked = !this.isChecked;
        },
    },
};
</script>

<style scoped lang="sass">

</style>
