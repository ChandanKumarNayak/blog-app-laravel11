import Echo from "laravel-echo";

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ["ws"],
});

window.Echo.channel("posts-channel").listen(".post-deleted", (event) => {
    console.log("Received post-deleted event:", event);
    loadLatestPosts();
});

window.Echo.private("post-create").listen(".post-created", (event) => {
    console.log("Received post-created event:", event);

    if (event.status === "postCreated") loadLatestPosts();
});