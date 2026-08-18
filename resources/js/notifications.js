const userId = window.TaskForge?.userId;

if (userId) {
    window.Echo
        .private(`App.Models.User.${userId}`)
        .notification((notification) => {
            console.log('Realtime notification received:', notification);
            window.Livewire?.dispatch('notification-received', { notification });
        });
}