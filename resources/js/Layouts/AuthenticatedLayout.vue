<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

const mainNavItems = [
    { label: 'Dashboard', icon: 'grid', route: 'dashboard' },
    { label: 'My Links', icon: 'link', route: 'links.index' },
    { label: 'Create Link', icon: 'plus', route: 'links.create' },
    { label: 'Bulk Create', icon: 'layers', route: 'links.bulk' },
];

const toolsNavItems = [
    { label: 'Analytics', icon: 'bar-chart', route: 'dashboard' },
    { label: 'Affiliate', icon: 'dollar-sign', route: 'affiliate.index' },
];

const supportNavItems = [
    { label: 'Help Center', icon: 'help-circle', route: 'help.center' },
    { label: 'API Docs', icon: 'code', route: 'api.docs' },
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
    plus: `<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>`,
    layers: `<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>`,
    'help-circle': `<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>`,
    code: `<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>`,
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
            <!-- Gold rule top accent -->
            <div class="sidebar__rule-top"></div>

            <!-- Logo + Collapse Toggle -->
            <div class="sidebar__header">
                <Link :href="route('dashboard')" class="sidebar__logo">
                    <Transition name="slide-fade">
                        <span v-if="!sidebarCollapsed" class="sidebar__logo-text">ShortLink</span>
                    </Transition>
                    <span v-if="sidebarCollapsed" class="sidebar__logo-monogram">SL</span>
                </Link>
                <button class="sidebar__collapse-btn" @click="toggleSidebar" :title="sidebarCollapsed ? 'Expand' : 'Collapse'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon" :class="{ 'icon--flipped': sidebarCollapsed }" v-html="icons['chevron-left']" />
                </button>
            </div>

            <!-- Gold rule divider -->
            <div class="sidebar__rule"></div>

            <!-- Main Section -->
            <Transition name="slide-fade">
                <div v-if="!sidebarCollapsed" class="sidebar__section-label">Main</div>
            </Transition>
            <nav class="sidebar__nav">
                <Link
                    v-for="item in mainNavItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="sidebar__nav-item"
                    :class="{ 'sidebar__nav-item--active': route().current(item.route) || (item.route === 'links.index' && route().current('links.*') && !route().current('links.create') && !route().current('links.bulk')) }"
                    :title="sidebarCollapsed ? item.label : ''"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="sidebar__nav-icon" v-html="icons[item.icon]" />
                    <Transition name="slide-fade">
                        <span v-if="!sidebarCollapsed" class="sidebar__nav-label">{{ item.label }}</span>
                    </Transition>
                </Link>
            </nav>

            <!-- Gold rule divider -->
            <div class="sidebar__rule"></div>

            <!-- Tools Section -->
            <Transition name="slide-fade">
                <div v-if="!sidebarCollapsed" class="sidebar__section-label">Tools</div>
            </Transition>
            <nav class="sidebar__nav">
                <Link
                    v-for="item in toolsNavItems"
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

            <!-- Gold rule divider -->
            <div class="sidebar__rule"></div>

            <!-- Support Section -->
            <Transition name="slide-fade">
                <div v-if="!sidebarCollapsed" class="sidebar__section-label">Support</div>
            </Transition>
            <nav class="sidebar__nav">
                <Link
                    v-for="item in supportNavItems"
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

            <!-- Gold rule before footer -->
            <div class="sidebar__rule"></div>

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

<style>
/* ── Global CSS variables (must be non-scoped so :root matches) ── */
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@400;500;700&display=swap');

:root {
    --sidebar-width: 240px;
    --sidebar-collapsed-width: 64px;
    --transition: 200ms ease;
    --bg-light: #fafafa;
    --sidebar-bg: #1a1a1a;
    --text-dark: #1a1a1a;
    --text-muted-dark: #888;
    --text-muted-light: #999;
    --accent-red: #e74c3c;
    --accent-gold: #d4af37;
    --rule-color: #333;
}
</style>

<style scoped>
/* ── Editorial Magazine Design Tokens ──────────── */

/* ── Layout shell ───────────────────────────────── */
.app-shell {
    display: flex;
    min-height: 100vh;
    background: var(--bg-light);
    color: var(--text-dark);
    font-family: 'Crimson Pro', serif;
}

/* ── Sidebar ────────────────────────────────────── */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: var(--sidebar-width);
    background: var(--sidebar-bg);
    border-right: 1px solid #2a2a2a;
    display: flex;
    flex-direction: column;
    transition: width var(--transition);
    z-index: 50;
    overflow: hidden;
}

.sidebar--collapsed {
    width: var(--sidebar-collapsed-width);
}

/* ── Gold top rule accent ───────────────────────── */
.sidebar__rule-top {
    height: 3px;
    background: linear-gradient(90deg, var(--accent-gold) 0%, transparent 100%);
    flex-shrink: 0;
}

/* ── Gold horizontal rule dividers ─────────────── */
.sidebar__rule {
    height: 1px;
    background: var(--accent-gold);
    opacity: 0.25;
    margin: 0 16px;
    flex-shrink: 0;
}

/* ── Section label ──────────────────────────────── */
.sidebar__section-label {
    padding: 16px 20px 6px;
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--text-muted-dark);
    white-space: nowrap;
    overflow: hidden;
    opacity: 0.7;
}

/* ── Sidebar Header ─────────────────────────────── */
.sidebar__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px 16px 20px;
    min-height: 72px;
    flex-shrink: 0;
}

.sidebar__logo {
    display: flex;
    align-items: center;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    flex: 1;
    min-width: 0;
}

.sidebar__logo-text {
    font-family: 'Oswald', sans-serif;
    font-weight: 700;
    font-size: 15px;
    color: #fff;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
    overflow: hidden;
}

.sidebar__logo-monogram {
    font-family: 'Oswald', sans-serif;
    font-weight: 700;
    font-size: 14px;
    color: var(--accent-gold);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    flex-shrink: 0;
}

.sidebar__collapse-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted-dark);
    padding: 4px;
    display: flex;
    align-items: center;
    flex-shrink: 0;
    transition: color var(--transition);
}

.sidebar__collapse-btn:hover {
    color: var(--accent-gold);
}

/* ── Nav Items ──────────────────────────────────── */
.sidebar__nav {
    flex: 1;
    padding: 8px 0;
    display: flex;
    flex-direction: column;
    gap: 0;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar__nav-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 20px;
    margin: 2px 8px;
    border-radius: 6px;
    border-left: none;
    text-decoration: none;
    color: var(--text-muted-dark);
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0;
    text-transform: none;
    white-space: nowrap;
    transition: all 200ms ease;
    cursor: pointer;
    position: relative;
}

.sidebar__nav-item:hover {
    color: #fff;
    background: rgba(255,255,255,0.06);
}

.sidebar__nav-item--active {
    color: #fff;
    background: rgba(212, 175, 55, 0.15);
    box-shadow: inset 0 0 0 1px rgba(212, 175, 55, 0.3);
}

.sidebar__nav-item--active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 20px;
    background: var(--accent-gold);
    border-radius: 0 2px 2px 0;
}

.sidebar__nav-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    opacity: 0.7;
}

.sidebar__nav-item--active .sidebar__nav-icon {
    opacity: 1;
    color: var(--accent-gold);
}

.sidebar__nav-label {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    font-weight: 500;
}

/* ── Footer ─────────────────────────────────────── */
.sidebar__footer {
    padding: 12px 0 16px;
    display: flex;
    flex-direction: column;
    gap: 0;
    flex-shrink: 0;
}

.sidebar__user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    overflow: hidden;
}

.sidebar__avatar {
    width: 32px;
    height: 32px;
    background: transparent;
    border: 1px solid var(--accent-gold);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0;
    color: var(--accent-gold);
    flex-shrink: 0;
}

.sidebar__user-info {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-width: 0;
}

.sidebar__user-name {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar__user-email {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    letter-spacing: 0;
    color: var(--text-muted-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar__logout {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 20px;
    margin: 2px 8px;
    border-radius: 6px;
    border: none;
    background: none;
    text-decoration: none;
    color: var(--text-muted-dark);
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0;
    text-transform: none;
    white-space: nowrap;
    cursor: pointer;
    width: calc(100% - 16px);
    transition: all 200ms ease;
}

.sidebar__logout:hover {
    color: var(--accent-red);
    background: rgba(231, 76, 60, 0.1);
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
    background: var(--bg-light);
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    padding: 0 32px;
    gap: 16px;
    position: sticky;
    top: 0;
    z-index: 40;
}

.topbar__menu-btn {
    display: none;
    background: none;
    border: none;
    color: var(--text-muted-light);
    cursor: pointer;
    padding: 4px;
    transition: color var(--transition);
}

.topbar__menu-btn:hover {
    color: var(--text-dark);
}

.topbar__title {
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 0;
    text-transform: none;
    color: var(--text-dark);
}

/* ── Page Content ───────────────────────────────── */
.page-content {
    flex: 1;
    padding: 36px 40px;
    background: var(--bg-light);
}

/* ── Icons ──────────────────────────────────────── */
.icon {
    width: 18px;
    height: 18px;
}

.icon--flipped {
    transform: rotate(180deg);
}

/* ── Mobile Backdrop ────────────────────────────── */
.mobile-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
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
        padding: 20px 20px;
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
