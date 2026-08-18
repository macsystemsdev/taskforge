let currentProjectChannel = null;
let currentProjectPresence = null;
const typingTimeouts = new Map();

// Helper function to get user ID with multiple fallbacks
function getUserId() {
    // Check multiple sources
    return window.TaskForge?.userId || 
           document.querySelector('meta[name="user-id"]')?.content ||
           document.querySelector('meta[name="user-id"]')?.getAttribute('content') ||
           document.getElementById('taskforge-project')?.dataset?.userId ||
           null;
}

function getUserName() {
    return window.TaskForge?.userName || 
           document.querySelector('meta[name="user-name"]')?.content ||
           document.querySelector('meta[name="user-name"]')?.getAttribute('content') ||
           document.getElementById('taskforge-project')?.dataset?.userName ||
           'Unknown User';
}

function dispatchPresenceEvent(event, payload) {
    window.Livewire.dispatch(event, payload);
}

function handleProjectTyping(user) {
    if (!user?.id) {
        console.warn('[Presence] Received typing event without valid user');
        return;
    }

    const userId = user.id;

    window.Livewire.dispatch('project-user-typing', { user });

    if (typingTimeouts.has(userId)) {
        clearTimeout(typingTimeouts.get(userId));
    }

    const timeout = setTimeout(() => {
        window.Livewire.dispatch('project-user-stopped-typing', { userId });
        typingTimeouts.delete(userId);
    }, 2500);

    typingTimeouts.set(userId, timeout);
}

function joinProjectPresenceChannel() {
    const projectElement = document.getElementById('taskforge-project');
    const projectId = projectElement?.dataset?.projectId;

    if (!projectId) {
        leaveProjectPresenceChannel();
        return;
    }

    const channelName = `presence-project.${projectId}`;

    if (currentProjectChannel === channelName) {
        return;
    }

    leaveProjectPresenceChannel();

    currentProjectChannel = channelName;

    console.log(`[Presence] Joining ${channelName}`);

    currentProjectPresence = window.Echo
        .join(channelName)
        .here((users) => {
            console.log(`[Presence] Users already in ${channelName}:`, users);
            dispatchPresenceEvent('project-presence-here', { users });
        })
        .joining((user) => {
            console.log(`[Presence] User joined ${channelName}:`, user);
            dispatchPresenceEvent('project-presence-joining', { user });
        })
        .leaving((user) => {
            console.log(`[Presence] User left ${channelName}:`, user);

            if (typingTimeouts.has(user.id)) {
                clearTimeout(typingTimeouts.get(user.id));
                typingTimeouts.delete(user.id);
                window.Livewire.dispatch('project-user-stopped-typing', { 
                    userId: user.id 
                });
            }

            dispatchPresenceEvent('project-presence-leaving', { user });
        })
        .listenForWhisper('typing', (event) => {
            handleProjectTyping(event.user);
        });
}

function leaveProjectPresenceChannel() {
    if (!currentProjectChannel) {
        return;
    }

    console.log(`[Presence] Leaving ${currentProjectChannel}`);

    window.Echo.leave(currentProjectChannel);

    currentProjectChannel = null;
    currentProjectPresence = null;
}

// Initialize
joinProjectPresenceChannel();

document.addEventListener('livewire:navigated', () => {
    joinProjectPresenceChannel();
});

// Export typing whisper functionality
window.TaskForge = {
    ...window.TaskForge,
    whisperProjectTyping() {
        if (!currentProjectPresence) {
            console.warn('[Presence] Not in a project presence channel');
            return;
        }

        const userId = getUserId();
        const userName = getUserName();
        
        if (!userId) {
            console.warn('[Presence] User ID not available');
            console.log('[Presence] Debug info:', {
                taskForgeUserId: window.TaskForge?.userId,
                metaUserId: document.querySelector('meta[name="user-id"]')?.content,
                projectElementUserId: document.getElementById('taskforge-project')?.dataset?.userId,
            });
            return;
        }

        currentProjectPresence.whisper('typing', {
            user: {
                id: userId,
                name: userName,
            },
        });
    },
};