<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    canLogin:         { type: Boolean, default: true },
    canRegister:      { type: Boolean, default: true },
    donationEnabled:  { type: Boolean, default: false },
    donationButtonId: { type: String,  default: '' },
});

const url     = ref('');
const result  = ref(null);
const error   = ref(null);
const loading = ref(false);
const copied  = ref(false);

const shorten = async () => {
    error.value  = null;
    result.value = null;
    loading.value = true;
    try {
        const res = await axios.post('/guest/shorten', { url: url.value });
        result.value = res.data;
    } catch (e) {
        error.value = e.response?.data?.error ?? 'Something went wrong';
    } finally {
        loading.value = false;
    }
};

const copy = async () => {
    await navigator.clipboard.writeText(result.value.short_url);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};
</script>

<template>
    <Head title="ShortLink — Editorial" />

    <div class="editorial">
        <!-- Fixed nav with mix-blend-mode -->
        <header class="masthead">
            <div class="masthead-logo">ShortLink</div>
            <nav class="masthead-nav">
                <template v-if="$page.props.auth?.user">
                    <Link :href="route('dashboard')">Dashboard</Link>
                </template>
                <template v-else>
                    <Link v-if="canLogin" :href="route('login')">Enter</Link>
                    <Link v-if="canRegister" :href="route('register')">Join</Link>
                </template>
            </nav>
        </header>

        <!-- Split hero: editorial magazine layout -->
        <section class="hero-split">
            <div class="hero-left">
                <div class="issue-label">Vol. 47 — Spring 2025</div>
                <h1>Knowledge<br><span>Shortened</span></h1>
                <p class="deck">A comprehensive study in digital brevity. How modern URL shortening transforms the way we share, track, and understand online presence.</p>
                
                <form class="editorial-form" @submit.prevent="shorten">
                    <label>Begin Your Journey</label>
                    <div class="input-wrap">
                        <input 
                            v-model="url" 
                            type="url" 
                            placeholder="Enter your destination..."
                            required
                        />
                        <button type="submit" :disabled="loading">
                            <span v-if="loading">Processing...</span>
                            <span v-else>Transform</span>
                        </button>
                    </div>
                    
                    <div v-if="error" class="form-error">{{ error }}</div>
                    
                    <div v-if="result" class="form-success">
                        <a :href="result.short_url" target="_blank">{{ result.short_url }}</a>
                        <button type="button" @click="copy" class="copy-btn">
                            {{ copied ? '✓ Copied' : 'Copy' }}
                        </button>
                    </div>
                </form>
                
                <p v-if="!$page.props.auth?.user" class="cta-text">
                    <Link :href="route('register')">Register for full access</Link> to analytics & saved links.
                </p>
                
                <p class="byline">Prepared by ShortLink Editorial</p>
                
                <div class="pull-quote">
                    "The most elegant solution is often the one that says less."
                </div>
            </div>

            <div class="hero-right">
                <div class="blueprint">
                    <h3>Blueprint Specifications</h3>
                    <p>Our apparatus features pneumatic tube delivery systems for instant URL redirection, analytical steam gauges for comprehensive click measurement, and brass-plated custom alias generators. Patent pending since the digital revolution.</p>
                    
                    <div class="spec-list">
                        <div class="spec-item">
                            <span class="spec-num">01</span>
                            <span class="spec-label">Response Time</span>
                            <span class="spec-val">&lt;50ms</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-num">02</span>
                            <span class="spec-label">Uptime</span>
                            <span class="spec-val">99.9%</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-num">03</span>
                            <span class="spec-label">Global Nodes</span>
                            <span class="spec-val">200+</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features band -->
        <section class="features-band">
            <div class="feature-edit">
                <div class="roman-num">I.</div>
                <h3>Velocity</h3>
                <p>Edge-cached redirects delivered with unprecedented speed and precision engineering.</p>
            </div>
            <div class="feature-edit">
                <div class="roman-num">II.</div>
                <h3>Insight</h3>
                <p>Deep analytics into every click, every origin, every digital journey undertaken.</p>
            </div>
            <div class="feature-edit">
                <div class="roman-num">III.</div>
                <h3>Identity</h3>
                <p>Custom aliases that speak your brand's unique language and aesthetic.</p>
            </div>
            <div class="feature-edit">
                <div class="roman-num">IV.</div>
                <h3>Liberty</h3>
                <p>No cost to enter. Essential tools accessible to all, without compromise.</p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="editorial-footer">
            <p>© MMXXV ShortLink — All Rights Reserved</p>
            <form v-if="donationEnabled && donationButtonId" action="https://www.paypal.com/donate" method="post" target="_blank">
                <input type="hidden" name="hosted_button_id" :value="donationButtonId" />
                <button type="submit" class="donate-link">Support via PayPal</button>
            </form>
        </footer>
    </div>
</template>

<style scoped>
/* ── DECONSTRUCTIVIST EDITORIAL ───────────────────────────── */

@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@500;700&display=swap');

* {
    box-sizing: border-box;
}

.editorial {
    font-family: 'Crimson Pro', serif;
    background: #fafafa;
    color: #1a1a1a;
    min-height: 100vh;
}

/* ── Masthead Navigation ───────────────────────────────────── */
.masthead {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    padding: 20px 60px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    mix-blend-mode: difference;
    z-index: 100;
    color: #fff;
}

.masthead-logo {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 8px;
    text-transform: uppercase;
}

.masthead-nav {
    display: flex;
    gap: 40px;
}

.masthead-nav a {
    color: inherit;
    text-decoration: none;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-family: 'Oswald', sans-serif;
    position: relative;
}

.masthead-nav a::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 1px;
    background: currentColor;
    transition: width 0.3s;
}

.masthead-nav a:hover::after {
    width: 100%;
}

/* ── Split Hero Layout ────────────────────────────────────── */
.hero-split {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
    position: relative;
}

.hero-left {
    padding: 200px 80px 80px;
    position: relative;
}

.issue-label {
    position: absolute;
    top: 120px;
    left: 80px;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #999;
    transform: rotate(-90deg);
    transform-origin: left top;
}

h1 {
    font-family: 'Oswald', sans-serif;
    font-size: 120px;
    font-weight: 700;
    line-height: 0.9;
    letter-spacing: -4px;
    margin-bottom: 30px;
}

h1 span {
    display: block;
    font-family: 'Crimson Pro', serif;
    font-size: 60px;
    font-weight: 400;
    font-style: italic;
    letter-spacing: 0;
    margin-top: 20px;
    color: #e74c3c;
}

.deck {
    font-size: 22px;
    line-height: 1.5;
    max-width: 400px;
    margin-bottom: 40px;
    color: #666;
}

.byline {
    font-size: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #999;
    border-top: 1px solid #ddd;
    padding-top: 20px;
    margin-top: 40px;
}

.pull-quote {
    position: absolute;
    bottom: 80px;
    right: 80px;
    max-width: 200px;
    font-size: 18px;
    font-style: italic;
    color: #666;
    line-height: 1.6;
}

.pull-quote::before {
    content: '"';
    font-size: 80px;
    font-family: 'Oswald', sans-serif;
    color: #e74c3c;
    position: absolute;
    top: -40px;
    left: -30px;
}

/* ── Editorial Form ────────────────────────────────────────── */
.editorial-form {
    margin-bottom: 30px;
}

.editorial-form label {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #999;
    margin-bottom: 16px;
}

.input-wrap {
    display: flex;
    border: 1px solid rgba(0,0,0,0.2);
    background: #fff;
    padding: 4px;
}

.input-wrap input {
    flex: 1;
    border: none;
    padding: 20px;
    font-family: inherit;
    font-size: 18px;
    outline: none;
    color: #1a1a1a;
}

.input-wrap input::placeholder {
    color: #bbb;
    font-style: italic;
}

.input-wrap button {
    background: #e74c3c;
    color: #fff;
    border: none;
    padding: 20px 40px;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.3s;
}

.input-wrap button:hover:not(:disabled) {
    background: #c0392b;
}

.input-wrap button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.form-error {
    margin-top: 16px;
    padding: 12px 16px;
    background: rgba(231, 76, 60, 0.1);
    border-left: 3px solid #e74c3c;
    color: #e74c3c;
}

.form-success {
    margin-top: 16px;
    padding: 20px;
    background: rgba(46, 204, 113, 0.1);
    border: 1px solid rgba(46, 204, 113, 0.3);
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.form-success a {
    flex: 1;
    font-size: 18px;
    font-weight: 600;
    color: #27ae60;
    text-decoration: none;
    word-break: break-all;
}

.form-success a:hover {
    text-decoration: underline;
}

.copy-btn {
    padding: 10px 20px;
    background: transparent;
    border: 1px solid #27ae60;
    color: #27ae60;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s;
}

.copy-btn:hover {
    background: #27ae60;
    color: #fff;
}

.cta-text {
    margin-top: 20px;
    font-size: 16px;
    color: #888;
}

.cta-text a {
    color: #e74c3c;
    text-decoration: none;
    font-weight: 600;
}

.cta-text a:hover {
    text-decoration: underline;
}

/* ── Right Panel / Blueprint ───────────────────────────────── */
.hero-right {
    background: #1a1a1a;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 80px;
}

.hero-right::before {
    content: '';
    position: absolute;
    top: 60px;
    right: 60px;
    bottom: 60px;
    left: 60px;
    border: 1px solid #333;
}

.blueprint {
    width: 100%;
    max-width: 400px;
    position: relative;
    z-index: 1;
    color: #888;
}

.blueprint h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #d4af37;
    margin-bottom: 20px;
}

.blueprint > p {
    font-size: 16px;
    line-height: 1.8;
    margin-bottom: 40px;
}

.spec-list {
    border-top: 1px solid #333;
    padding-top: 20px;
}

.spec-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 12px 0;
    border-bottom: 1px solid #222;
}

.spec-num {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    color: #555;
    width: 30px;
}

.spec-label {
    flex: 1;
    font-size: 14px;
    color: #888;
}

.spec-val {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    color: #d4af37;
    letter-spacing: 2px;
}

/* ── Features Band ─────────────────────────────────────────── */
.features-band {
    background: #f0f0f0;
    padding: 100px 60px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 40px;
}

.feature-edit {
    position: relative;
}

.feature-edit::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 30px;
    height: 3px;
    background: #1a1a1a;
}

.roman-num {
    font-size: 12px;
    font-weight: 700;
    color: #e74c3c;
    margin-bottom: 20px;
    letter-spacing: 1px;
}

.feature-edit h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0 0 16px;
}

.feature-edit p {
    font-size: 16px;
    line-height: 1.6;
    color: #666;
}

/* ── Footer ────────────────────────────────────────────────── */
.editorial-footer {
    background: #1a1a1a;
    color: #666;
    padding: 60px;
    text-align: center;
    font-size: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-family: 'Oswald', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 40px;
    flex-wrap: wrap;
}

.donate-link {
    background: transparent;
    border: 1px solid #444;
    color: #888;
    padding: 12px 24px;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s;
}

.donate-link:hover {
    border-color: #d4af37;
    color: #d4af37;
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 968px) {
    .masthead {
        padding: 20px 30px;
    }

    .masthead-logo {
        font-size: 12px;
        letter-spacing: 4px;
    }

    .masthead-nav {
        gap: 20px;
    }

    .hero-split {
        grid-template-columns: 1fr;
    }

    .hero-left {
        padding: 140px 30px 60px;
    }

    .issue-label {
        top: 100px;
        left: 30px;
    }

    h1 {
        font-size: 60px;
    }

    h1 span {
        font-size: 36px;
    }

    .pull-quote {
        position: static;
        margin-top: 40px;
        max-width: 100%;
    }

    .hero-right {
        padding: 60px 30px;
    }

    .features-band {
        grid-template-columns: 1fr 1fr;
        padding: 60px 30px;
        gap: 30px;
    }

    .input-wrap {
        flex-direction: column;
    }

    .input-wrap button {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .features-band {
        grid-template-columns: 1fr;
    }

    h1 {
        font-size: 48px;
    }

    .masthead-nav {
        gap: 15px;
    }

    .masthead-nav a {
        font-size: 10px;
    }
}
</style>
