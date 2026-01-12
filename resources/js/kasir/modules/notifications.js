export const notificationsModule = {
    notifications: [],
    addNotification(message, type = "error") {
        const id = Date.now();
        this.notifications.push({ id, message, type });
        this.$nextTick(() => window.lucide && lucide.createIcons());

        setTimeout(() => {
            this.notifications = this.notifications.filter((n) => n.id !== id);
        }, 4000);
    },
};
