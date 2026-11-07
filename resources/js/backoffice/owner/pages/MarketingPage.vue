<script setup>
import { onMounted, ref } from "vue";
import { backofficeForm, backofficeRequest } from "../../api";

const tab = ref("banners");
const banners = ref([]);
const promotions = ref([]);
const error = ref("");
const bannerDraft = ref({ id: null, isActive: true, desktop: null, mobile: null });
const promotionDraft = ref({ id: null, title: "", body: "", isActive: true, image: null });

onMounted(load);

async function load() {
    error.value = "";
    try {
        const payload = await backofficeRequest("GET", "/api/backoffice/marketing");
        banners.value = payload.banners ?? [];
        promotions.value = payload.promotions ?? [];
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

async function saveBanner() {
    error.value = "";
    const body = new FormData();
    body.append("is_active", bannerDraft.value.isActive ? "1" : "0");
    if (bannerDraft.value.desktop) body.append("image_desktop", bannerDraft.value.desktop);
    if (bannerDraft.value.mobile) body.append("image_mobile", bannerDraft.value.mobile);
    const path = bannerDraft.value.id
        ? `/api/backoffice/marketing/banners/${bannerDraft.value.id}`
        : "/api/backoffice/marketing/banners";
    try {
        await backofficeForm(path, body);
        bannerDraft.value = { id: null, isActive: true, desktop: null, mobile: null };
        await load();
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

async function savePromotion() {
    error.value = "";
    const body = new FormData();
    body.append("title", promotionDraft.value.title);
    body.append("body", promotionDraft.value.body);
    body.append("is_active", promotionDraft.value.isActive ? "1" : "0");
    if (promotionDraft.value.image) body.append("image", promotionDraft.value.image);
    const path = promotionDraft.value.id
        ? `/api/backoffice/marketing/promotions/${promotionDraft.value.id}`
        : "/api/backoffice/marketing/promotions";
    try {
        await backofficeForm(path, body);
        promotionDraft.value = { id: null, title: "", body: "", isActive: true, image: null };
        await load();
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Маркетинг</h1>
        <div class="flex gap-1">
            <button
                type="button"
                class="inline-flex h-9 items-center rounded px-3 text-sm"
                :class="tab === 'banners' ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700'"
                @click="tab = 'banners'"
            >
                Баннеры
            </button>
            <button
                type="button"
                class="inline-flex h-9 items-center rounded px-3 text-sm"
                :class="tab === 'promotions' ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700'"
                @click="tab = 'promotions'"
            >
                Акции
            </button>
        </div>
        <p
            v-if="error"
            class="text-sm text-red-700"
        >
            {{ error }}
        </p>
        <template v-if="tab === 'banners'">
            <div class="overflow-x-auto rounded border border-neutral-200 bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                        <tr>
                            <th class="px-3 py-2 font-medium">Десктоп</th>
                            <th class="px-3 py-2 font-medium">Телефон</th>
                            <th class="px-3 py-2 font-medium">Активен</th>
                            <th class="px-3 py-2 font-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="banner in banners"
                            :key="banner.id"
                            class="border-b border-neutral-100 last:border-0"
                        >
                            <td class="px-3 py-2"><img
                                :src="banner.image_desktop"
                                alt=""
                                class="h-12 w-20 object-cover"
                            ></td>
                            <td class="px-3 py-2"><img
                                :src="banner.image_mobile"
                                alt=""
                                class="h-12 w-12 object-cover"
                            ></td>
                            <td class="px-3 py-2">{{ banner.is_active ? "Да" : "Нет" }}</td>
                            <td class="px-3 py-2">
                                <button
                                    type="button"
                                    class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                                    @click="bannerDraft = { id: banner.id, isActive: banner.is_active, desktop: null, mobile: null }"
                                >
                                    Править
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <form
                class="space-y-3"
                @submit.prevent="saveBanner"
            >
                <h2 class="text-sm font-medium">{{ bannerDraft.id ? `Баннер #${bannerDraft.id}` : "Новый баннер" }}</h2>
                <label class="flex items-center gap-2 text-sm"><input
                    v-model="bannerDraft.isActive"
                    type="checkbox"
                > Активен</label>
                <label class="block text-sm">Десктоп
                    <input
                        type="file"
                        accept="image/*"
                        class="mt-1 block text-sm"
                        @change="bannerDraft.desktop = $event.target.files?.[0] ?? null"
                    >
                </label>
                <label class="block text-sm">Телефон
                    <input
                        type="file"
                        accept="image/*"
                        class="mt-1 block text-sm"
                        @change="bannerDraft.mobile = $event.target.files?.[0] ?? null"
                    >
                </label>
                <button
                    type="submit"
                    class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
                >
                    Сохранить
                </button>
            </form>
        </template>
        <template v-else>
            <div class="overflow-x-auto rounded border border-neutral-200 bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                        <tr>
                            <th class="px-3 py-2 font-medium">Заголовок</th>
                            <th class="px-3 py-2 font-medium">Активна</th>
                            <th class="px-3 py-2 font-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="promotion in promotions"
                            :key="promotion.id"
                            class="border-b border-neutral-100 last:border-0"
                        >
                            <td class="px-3 py-2">{{ promotion.title }}</td>
                            <td class="px-3 py-2">{{ promotion.is_active ? "Да" : "Нет" }}</td>
                            <td class="px-3 py-2">
                                <button
                                    type="button"
                                    class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                                    @click="promotionDraft = { id: promotion.id, title: promotion.title, body: promotion.body, isActive: promotion.is_active, image: null }"
                                >
                                    Править
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <form
                class="space-y-3"
                @submit.prevent="savePromotion"
            >
                <h2 class="text-sm font-medium">{{ promotionDraft.id ? `Акция #${promotionDraft.id}` : "Новая акция" }}</h2>
                <input
                    v-model="promotionDraft.title"
                    required
                    placeholder="Заголовок"
                    class="h-9 w-full rounded border border-neutral-300 px-3 text-sm"
                >
                <textarea
                    v-model="promotionDraft.body"
                    rows="4"
                    placeholder="Текст"
                    class="w-full rounded border border-neutral-300 px-3 py-2 text-sm"
                />
                <label class="flex items-center gap-2 text-sm"><input
                    v-model="promotionDraft.isActive"
                    type="checkbox"
                > Активна</label>
                <input
                    type="file"
                    accept="image/*"
                    class="text-sm"
                    @change="promotionDraft.image = $event.target.files?.[0] ?? null"
                >
                <button
                    type="submit"
                    class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
                >
                    Сохранить
                </button>
            </form>
        </template>
    </section>
</template>
