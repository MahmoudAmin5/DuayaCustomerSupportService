import './bootstrap';
import './echo';
import Echo from "laravel-echo";
import Pusher from "pusher-js";

// خلي Pusher متاح كـ global (مهم مع Laravel Echo)
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,   // جاي من .env
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
