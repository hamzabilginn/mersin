import { createRouter, createWebHashHistory } from 'vue-router';
import Dashboard from '@/views/Dashboard.vue';
import Tasks from '@/views/Tasks.vue';
import Chat from '@/views/Chat.vue';
import AICopilot from '@/views/AICopilot.vue';
import Analytics from '@/views/Analytics.vue';
import Documentation from '@/views/Documentation.vue';
import Guide from '@/views/Guide.vue';

const routes = [
    { path: '/', name: 'dashboard', component: Dashboard },
    { path: '/tasks', name: 'tasks', component: Tasks },
    { path: '/chat', name: 'chat', component: Chat },
    { path: '/ai', name: 'ai', component: AICopilot },
    { path: '/analytics', name: 'analytics', component: Analytics },
    { path: '/documentation', name: 'documentation', component: Documentation },
    { path: '/guide', name: 'guide', component: Guide }
];

const router = createRouter({
    history: createWebHashHistory(), // Hash history is more robust for offline SPAs served from a single Blade file
    routes
});

export default router;
