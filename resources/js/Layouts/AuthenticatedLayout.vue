<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

const navItems = [
    { label: 'Dashboard', icon: 'grid', route: 'dashboard' },
    { label: 'My Links', icon: 'link', route: 'links.index' },
    { label: 'Analytics', icon: 'bar-chart', route: 'dashboard' },
    { label: 'Affiliate', icon: 'dollar-sign', route: 'dashboard' },
    { label: 'Settings', icon: 'settings', route: 'profile.edit' },
];

const toggleSidebar = () => { sidebarCollapsed.value = !sidebarCollapsed.value; };
const toggleMobileSidebar = () => { mobileSidebarOpen.value = !mobileSidebarOpen.value; };

const icons = {
    grid: `<path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>`,
    link: `<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>`,
    'bar-chart': `<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>`,
    'dollar-sign': `<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>`,
    settings: `<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>`,
    'log-out': `<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>`,
    menu: `<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>`,
    'chevron-left': `<polyline points="15 18 9 12 15 6"/>`,
};
</script>

<template>
    <div class="app-shell">

        <!-- Mobile backdrop -->
        <Transition name="fade">
            <div
                v-if="mobileSidebarOpen"
                class="mobile-backdrop"
                @click="mobileSidebarOpen = false"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="sidebar"
            :class="{ 'sidebar--collapsed': sidebarCollapsed, 'sidebar--open': mobileSidebarOpen }"
        >
            <!-- Logo + Collapse Toggle -->
            <div class="sidebar__header">
                <Link :href="route('dashboard')" class="sidebar__logo">
                    <span class="sidebar__logo-icon">⚡</span>
                    <Transition name="slide-fade">
                        <span v-if="!sidebarCollapsed" class="sidebar__logo-text">ShortLink</span>
                    </Transition>
                </Link>
                <button class="sidebar__collapse-btn" @click="toggleSidebar" :title="sidebarCollapsed ? 'Expand' : 'Collapse'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon" :class="{ 'icon--flipped': sidebarCollapsed }" v-html="icons['chevron-left']" />
                </button>
            </div>

            <!-- Nav Items -->
            <nav class="sidebar__nav">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="sidebar__nav-item"
                    :class="{ 'sidebar__nav-item--active': route().current(item.route) }"
                    :title="sidebarCollapsed ? item.label : ''"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="sidebar__nav-icon" v-html="icons[item.icon]" />
                    <Transition name="slide-fade">
                        <span v-if="!sidebarCollapsed" class="sidebar__nav-label">{{ item.label }}</span>
                    </Transition>
                </Link>
            </nav>

            <!-- User Footer -->
            <div class="sidebar__footer">
                <div class="sidebar__user" :class="{ 'sidebar__user--collapsed': sidebarCollapsed }">
                    <div class="sidebar__avatar">
                        {{ user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                    </div>
                    <Transition name="slide-fade">
                        <div v-if="!sidebarCollapsed" class="sidebar__user-info">
                            <span class="sidebar__user-name">{{ user?.name }}</span>
                            <span class="sidebar__user-email">{{ user?.email }}</span>
                        </div>
                    </Transition>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="sidebar__logout"
                    :title="sidebarCollapsed ? 'Log Out' : ''"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="sidebar__nav-icon" v-html="icons['log-out']" />
                    <Transition name="slide-fade">
                        <span v-if="!sidebarCollapsed">Log Out</span>
                    </Transition>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content" :class="{ 'main-content--expanded': sidebarCollapsed }">

            <!-- Mobile Top Bar -->
            <header class="topbar">
                <button class="topbar__menu-btn" @click="toggleMobileSidebar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon" v-html="icons['menu']" />
                </button>
                <div class="topbar__title">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* ── Design tokens ──────────────────────────────── */
:root {
    --bg-primary: #0A0A0A;
    --bg-secondary: #141414;
    --bg-tertiary: #1A1A1A;
    --text-primary: #FAFAFA;
    --text-secondary: #A1A1AA;
    --accent-cyan: #22D3EE;
    --accent-success: #22C55E;
    --accent-warning: #F59E0B;
    --accent-danger: #EF4444;
    --sidebar-width: 240px;
    --sidebar-collapsed-width: 64px;
    --transition: 200ms ease;
}

/* ── Layout shell ───────────────────────────────── */
.app-shell {
    display: flex;
    min-height: 100vh;
    background: #0A0A0A;
    color: #FAFAFA;
    font-family: 'IBM Plex Sans', -apple-system, sans-serif;
}

/* ── Sidebar ────────────────────────────────────── */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: var(--sidebar-width);
    background: #141414;
    border-right: 1px solid rgba(255,255,255,0.06);
    display: flex;
    flex-direction: column;
    transition: width var(--transition);
    z-index: 50;
    overflow: hidden;
}

.sidebar--collapsed {
    width: var(--sidebar-collapsed-width);
}

/* ── Sidebar Header ─────────────────────────────── */
.sidebar__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    min-height: 64px;
}

.sidebar__logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
}

.sidebar__logo-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.sidebar__logo-text {
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 700;
    font-size: 15px;
    color: #FAFAFA;
    letter-spacing: -0.02em;
}

.sidebar__collapse-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #A1A1AA;
    padding: 4px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    flex-shrink: 0;
    transition: color var(--transition), background var(--transition);
}

.sidebar__collapse-btn:hover {
    color: #FAFAFA;
    background: rgba(255,255,255,0.06);
}

/* ── Nav Items ──────────────────────────────────── */
.sidebar__nav {
    flex: 1;
    padding: 12px 8px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar__nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    border-left: 2px solid transparent;
    text-decoration: none;
    color: #A1A1AA;
    font-size: 14px;
    white-space: nowrap;
    transition: all var(--transition);
    cursor: pointer;
}

.sidebar__nav-item:hover {
    color: #FAFAFA;
    background: rgba(255,255,255,0.05);
}

.sidebar__nav-item--active {
    color: #22D3EE;
    border-left-color: #22D3EE;
    background: rgba(34, 211, 238, 0.07);
}

.sidebar__nav-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.sidebar__nav-label {
    font-weight: 450;
}

/* ── Footer ─────────────────────────────────────── */
.sidebar__footer {
    padding: 12px 8px;
    border-top: 1px solid rgba(255,255,255,0.05);
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.sidebar__user {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    overflow: hidden;
    margin-bottom: 4px;
}

.sidebar__avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #22D3EE, #22C55E);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: #0A0A0A;
    flex-shrink: 0;
}

.sidebar__user-info {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-width: 0;
}

.sidebar__user-name {
    font-size: 13px;
    font-weight: 600;
    color: #FAFAFA;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar__user-email {
    font-size: 11px;
    color: #A1A1AA;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar__logout {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    border: none;
    background: none;
    text-decoration: none;
    color: #A1A1AA;
    font-size: 14px;
    white-space: nowrap;
    cursor: pointer;
    width: 100%;
    transition: all var(--transition);
}

.sidebar__logout:hover {
    color: #EF4444;
    background: rgba(239, 68, 68, 0.07);
}

/* ── Main Content ───────────────────────────────── */
.main-content {
    margin-left: var(--sidebar-width);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    transition: margin-left var(--transition);
}

.main-content--expanded {
    margin-left: var(--sidebar-collapsed-width);
}

/* ── Top Bar ────────────────────────────────────── */
.topbar {
    height: 64px;
    background: #141414;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    padding: 0 24px;
    gap: 16px;
    position: sticky;
    top: 0;
    z-index: 40;
}

.topbar__menu-btn {
    display: none;
    background: none;
    border: none;
    color: #A1A1AA;
    cursor: pointer;
    padding: 4px;
}

.topbar__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 18px;
    font-weight: 600;
    color: #FAFAFA;
}

/* ── Page Content ───────────────────────────────── */
.page-content {
    flex: 1;
    padding: 28px 32px;
    background: #0A0A0A;
}

/* ── Icons ──────────────────────────────────────── */
.icon {
    width: 20px;
    height: 20px;
}

.icon--flipped {
    transform: rotate(180deg);
}

/* ── Mobile Backdrop ────────────────────────────── */
.mobile-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 40;
}

/* ── Transitions ────────────────────────────────── */
.fade-enter-active, .fade-leave-active { transition: opacity 200ms; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-fade-enter-active { transition: all 150ms ease; }
.slide-fade-leave-active { transition: all 100ms ease; }
.slide-fade-enter-from { opacity: 0; transform: translateX(-8px); }
.slide-fade-leave-to { opacity: 0; transform: translateX(-4px); }

/* ── Responsive ─────────────────────────────────── */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        width: var(--sidebar-width) !important;
        transition: transform var(--transition);
    }

    .sidebar--open {
        transform: translateX(0);
    }

    .main-content,
    .main-content--expanded {
        margin-left: 0;
    }

    .topbar__menu-btn {
        display: flex;
    }

    .page-content {
        padding: 20px 16px;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .sidebar {
        width: var(--sidebar-collapsed-width);
    }

    .main-content {
        margin-left: var(--sidebar-collapsed-width);
    }
}
</style>
