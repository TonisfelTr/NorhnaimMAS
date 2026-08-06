export default {
    bind(el) {
        $(el).select2({
            placeholder: el.placeholder,
            allowClear: true,
        }).on('change', function () {
            // Trigger an input event to update the v-model
            const event = new Event('input', { bubbles: true });
            el.dispatchEvent(event);
        });
    },
    update(el, binding, vnode) {
        // Re-initialize select2 on update to handle dynamic changes
        $(el).select2({
            placeholder: binding.value.placeholder,
            allowClear: true,
        });
    },
    unbind(el) {
        // Destroy select2 instance on unbind
        $(el).select2('destroy');
    },
};
