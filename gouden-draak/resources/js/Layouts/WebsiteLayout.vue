<template>
    <div style="background-color: #8B0000; min-height: 100vh; margin: 0; padding: 8px;">

        <!-- Header balk -->
        <div style="background-color: #FF0000; width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 0 8px; min-height: 50px; box-sizing: border-box;">

            <!-- Logo (verborgen op heel klein scherm) -->
            <div class="header-logo" style="display: flex; align-items: center; gap: 6px;">
                <img src="/images/dragon-small.png" alt="Draak" style="height: 35px;" />
                <span class="gd-logo-font header-logo-text" style="color: #FFFF00;">De Gouden Draak</span>
            </div>

            <!-- Scrollende tekst -->
            <div style="flex: 1; overflow: hidden; text-align: center; margin: 0 8px;">
                <div class="marquee-wrapper">
                    <span style="color: #FFFF00; font-weight: bold; white-space: nowrap; font-size: 13px;">
                        🐉 &nbsp; Welkom bij De Gouden Draak &nbsp; — &nbsp; Chinees Indische Specialiteiten &nbsp; — &nbsp; Bekijk onze aanbiedingen! &nbsp; 🐉
                    </span>
                </div>
            </div>

            <!-- Logo rechts + hamburger -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <div class="header-logo" style="display: flex; align-items: center; gap: 6px;">
                    <span class="gd-logo-font header-logo-text" style="color: #FFFF00;">De Gouden Draak</span>
                    <img src="/images/dragon-small-flipped.png" alt="Draak" style="height: 35px;" />
                </div>
                <!-- Hamburger knop (alleen mobiel via CSS) -->
                <button class="hamburger-btn" @click="mobileMenuOpen = !mobileMenuOpen"
                    style="background: none; border: 1px solid #FFFF00; color: #FFFF00; padding: 4px 8px; cursor: pointer; font-size: 18px;">
                    {{ mobileMenuOpen ? '✕' : '☰' }}
                </button>
            </div>
        </div>

        <!-- Decoratieve gele rand boven -->
        <div style="height: 7px; background-color: #FF0000;"></div>
        <div style="display: flex; height: 20px;">
            <div style="width: 20px; border-top: 4px solid #FFFF00; border-left: 4px solid #FFFF00;"></div>
            <div style="flex: 1; border-top: 4px solid #FFFF00;"></div>
            <div style="width: 20px; border-top: 4px solid #FFFF00; border-right: 4px solid #FFFF00;"></div>
        </div>

        <!-- Hoofd content -->
        <div style="display: flex; background-color: #FF0000;">
            <!-- Linker gele lijn (verborgen op mobiel) -->
            <div class="side-border" style="width: 20px; border-left: 4px solid #FFFF00; flex-shrink: 0;"></div>

            <!-- Midden -->
            <div style="flex: 1; min-width: 0;">

                <!-- Sub-header met draken (verkleind op mobiel) -->
                <div class="sub-header" style="text-align: center; padding: 8px 0; position: relative; overflow: hidden;">
                    <img src="/images/dragon-small.png" alt="Draak links" class="dragon-img" style="float: left;" />
                    <img src="/images/dragon-small-flipped.png" alt="Draak rechts" class="dragon-img" style="float: right;" />
                    <div style="overflow: hidden; padding: 10px 5px;">
                        <p class="subtitle-text" style="color: #FFFF00; margin: 5px 0;">Chinees Indische Specialiteiten</p>
                        <p class="gd-logo-font title-text" style="color: #FFFF00; font-weight: bold; margin: 0;">De Gouden Draak</p>
                    </div>
                </div>

                <!-- Navigatie desktop -->
                <nav class="nav-desktop" style="display: flex; flex-wrap: wrap; justify-content: center; border: 1px solid white; margin-bottom: 8px;">
                    <Link v-for="item in navItems" :key="item.name"
                        :href="route(item.route)"
                        :style="{ backgroundColor: isActive(item.path) ? '#CC0000' : 'transparent' }"
                        class="gd-nav-link nav-link-item">
                        &nbsp;&nbsp;{{ item.name }}&nbsp;&nbsp;
                    </Link>
                    <!-- Taal knoppen -->
                    <div style="display: flex; align-items: center; padding: 0 8px; gap: 4px; margin-left: auto;">
                        <button v-for="lang in ['nl', 'en']" :key="lang"
                            @click="switchLocale(lang)"
                            :style="{
                                backgroundColor: locale === lang ? '#FFFF00' : 'transparent',
                                color: locale === lang ? '#8B0000' : 'white',
                                border: '1px solid #FFFF00',
                                padding: '2px 6px',
                                fontSize: '11px',
                                fontWeight: 'bold',
                                cursor: 'pointer'
                            }">
                            {{ lang.toUpperCase() }}
                        </button>
                    </div>
                </nav>

                <!-- Navigatie mobiel (uitklapbaar) -->
                <nav v-if="mobileMenuOpen" class="nav-mobile" style="border: 1px solid white; margin-bottom: 8px;">
                    <Link v-for="item in navItems" :key="item.name"
                        :href="route(item.route)"
                        :style="{ backgroundColor: isActive(item.path) ? '#CC0000' : 'transparent' }"
                        @click="mobileMenuOpen = false"
                        style="display: block; color: white; text-decoration: none; padding: 10px 15px; border-bottom: 1px solid rgba(255,255,255,0.2); font-size: 16px;">
                        {{ item.name }}
                    </Link>
                    <div style="display: flex; gap: 8px; padding: 8px 15px;">
                        <button v-for="lang in ['nl', 'en']" :key="lang"
                            @click="switchLocale(lang)"
                            :style="{
                                backgroundColor: locale === lang ? '#FFFF00' : 'transparent',
                                color: locale === lang ? '#8B0000' : 'white',
                                border: '1px solid #FFFF00',
                                padding: '4px 10px',
                                fontSize: '13px',
                                fontWeight: 'bold',
                                cursor: 'pointer'
                            }">
                            {{ lang.toUpperCase() }}
                        </button>
                    </div>
                </nav>

                <!-- Content paneel -->
                <div class="gd-content">
                    <slot />
                </div>

                <div style="height: 15px;"></div>
            </div>

            <!-- Rechter gele lijn (verborgen op mobiel) -->
            <div class="side-border" style="width: 20px; border-right: 4px solid #FFFF00; flex-shrink: 0;"></div>
        </div>

        <!-- Decoratieve gele rand onder -->
        <div style="display: flex; height: 20px;">
            <div style="width: 20px; border-bottom: 4px solid #FFFF00; border-left: 4px solid #FFFF00;"></div>
            <div style="flex: 1; border-bottom: 4px solid #FFFF00;"></div>
            <div style="width: 20px; border-bottom: 4px solid #FFFF00; border-right: 4px solid #FFFF00;"></div>
        </div>
        <div style="height: 7px; background-color: #FF0000;"></div>

    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()
const mobileMenuOpen = ref(false)

const navItems = [
    { name: 'Home',         route: 'home',         path: '/'            },
    { name: 'Menukaart',    route: 'menu',         path: '/menu'        },
    { name: 'Nieuws',       route: 'nieuws',       path: '/nieuws'      },
    { name: 'Aanbiedingen', route: 'aanbiedingen', path: '/aanbiedingen'},
    { name: 'Bestellen',    route: 'bestellen',    path: '/bestellen'   },
    { name: 'Contact',      route: 'contact',      path: '/contact'     },
]

function switchLocale(lang) {
    locale.value = lang
    router.post(route('locale.switch', lang), {}, { preserveState: true, preserveScroll: true })
}

function isActive(path) {
    return window.location.pathname === path
}
</script>

<style scoped>
.marquee-wrapper {
    display: inline-block;
    white-space: nowrap;
    animation: marquee 20s linear infinite;
}
@keyframes marquee {
    0%   { transform: translateX(100vw); }
    100% { transform: translateX(-100%); }
}

/* Desktop (>768px) */
.dragon-img       { height: 180px; }
.subtitle-text    { font-size: 32px; }
.title-text       { font-size: 42px; }
.header-logo-text { font-size: 26px; }
.nav-desktop      { display: flex !important; }
.hamburger-btn    { display: none; }
.side-border      { display: block; }

/* Tablet (480px - 768px) */
@media (max-width: 768px) {
    .dragon-img       { height: 100px; }
    .subtitle-text    { font-size: 18px; }
    .title-text       { font-size: 24px; }
    .header-logo-text { font-size: 16px; }
    .nav-desktop      { flex-wrap: wrap; }
    .nav-link-item    { font-size: 14px !important; padding: 6px 4px !important; }
    .side-border      { width: 10px !important; }
}

/* Mobiel (<480px) */
@media (max-width: 480px) {
    .dragon-img       { height: 60px; }
    .subtitle-text    { font-size: 13px; }
    .title-text       { font-size: 18px; }
    .header-logo-text { display: none; }
    .nav-desktop      { display: none !important; }
    .hamburger-btn    { display: block; }
    .side-border      { display: none; }
    .sub-header       { padding: 5px 0; }
}
</style>
