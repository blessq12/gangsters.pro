<script>
const COOKIE_NAME = "cookie_consent";
const COOKIE_DAYS = 180;
const METRIKA_ID = 57393940;
const AHREFS_KEY = "PxdjW7ifwlYeLIKMykwLkw";

export default {
    data() {
        return {
            visible: false,
            choice: null,
        };
    },
    mounted() {
        this.choice = this.readConsent();
        if (!this.choice) {
            this.visible = true;
            return;
        }
        if (this.choice === "accepted") {
            this.loadAnalytics();
        }
    },
    methods: {
        readConsent() {
            const match = document.cookie.match(
                new RegExp("(?:^|; )" + COOKIE_NAME + "=([^;]*)")
            );
            return match ? decodeURIComponent(match[1]) : null;
        },
        writeConsent(value) {
            const maxAge = COOKIE_DAYS * 24 * 60 * 60;
            document.cookie = `${COOKIE_NAME}=${encodeURIComponent(
                value
            )}; path=/; max-age=${maxAge}; SameSite=Lax`;
            this.choice = value;
        },
        acceptAll() {
            this.writeConsent("accepted");
            this.visible = false;
            this.loadAnalytics();
        },
        acceptNecessary() {
            this.writeConsent("necessary");
            this.visible = false;
        },
        loadAnalytics() {
            this.loadMetrika();
            this.loadAhrefs();
        },
        loadMetrika() {
            if (window.ym) {
                return;
            }
            (function (m, e, t, r, i, k, a) {
                m[i] =
                    m[i] ||
                    function () {
                        (m[i].a = m[i].a || []).push(arguments);
                    };
                m[i].l = 1 * new Date();
                for (let j = 0; j < document.scripts.length; j++) {
                    if (document.scripts[j].src === r) {
                        return;
                    }
                }
                k = e.createElement(t);
                a = e.getElementsByTagName(t)[0];
                k.async = 1;
                k.src = r;
                a.parentNode.insertBefore(k, a);
            })(
                window,
                document,
                "script",
                "https://mc.yandex.ru/metrika/tag.js",
                "ym"
            );

            window.ym(METRIKA_ID, "init", {
                clickmap: true,
                trackLinks: true,
                accurateTrackBounce: true,
                ecommerce: "dataLayer",
            });
        },
        loadAhrefs() {
            if (document.querySelector('script[data-ahrefs-analytics]')) {
                return;
            }
            const script = document.createElement("script");
            script.src = "https://analytics.ahrefs.com/analytics.js";
            script.async = true;
            script.dataset.key = AHREFS_KEY;
            script.dataset.ahrefsAnalytics = "1";
            document.head.appendChild(script);
        },
    },
};
</script>

<template>
    <div
        v-if="visible"
        class="fixed inset-x-0 bottom-0 z-[100] p-4 sm:p-6"
        role="dialog"
        aria-label="Уведомление о cookie"
    >
        <div
            class="mx-auto max-w-3xl rounded-xl bg-gray-900 text-white shadow-2xl p-4 sm:p-6 flex flex-col sm:flex-row gap-4 sm:items-center"
        >
            <p class="text-sm text-gray-200 flex-1">
                Мы используем необходимые cookie для работы сайта. Аналитические
                cookie (Яндекс.Метрика и др.) включаем только с вашего согласия.
                Подробнее —
                <a href="/cookies" class="underline text-blue-300"
                    >политика cookie</a
                >.
            </p>
            <div class="flex flex-col sm:flex-row gap-2 shrink-0">
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-sm"
                    @click="acceptNecessary"
                >
                    Только необходимые
                </button>
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-sm font-medium"
                    @click="acceptAll"
                >
                    Принять
                </button>
            </div>
        </div>
    </div>
</template>
