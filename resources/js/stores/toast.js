import { reactive } from "vue";

export const toastStore = reactive({
    message: null,
    type: null, // 'success' or 'error'
    visible: false,

    show(message, type = "success") {
        this.message = message;
        this.type = type;
        this.visible = true;

        // Auto-hide after 4 seconds
        setTimeout(() => {
            this.visible = false;
        }, 4000);
    },
});
