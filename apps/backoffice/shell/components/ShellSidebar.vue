<script setup>
import { RouterLink } from "vue-router";
import { useSessionStore } from "../store/sessionStore";
import ShellIcon from "./ShellIcon.vue";
import logoMark from "../../assets/logo-mark.svg";

defineProps({
    collapsed: { type: Boolean, default: false },
    mobileOpen: { type: Boolean, default: false },
});

const emit = defineEmits(["close"]);

const session = useSessionStore();
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        leave-to-class="opacity-0"
    >
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"
            aria-hidden="true"
            @click="emit('close')"
        />
    </Transition>

    <aside
        class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col border-r border-bo-border bg-bo-surface transition-[transform,width] duration-300 ease-out lg:static lg:z-auto lg:translate-x-0"
        :class="[
            mobileOpen ? 'translate-x-0 shadow-bo-lg' : '-translate-x-full',
            collapsed ? 'lg:w-20' : 'lg:w-64',
        ]"
        aria-label="Меню раздела"
    >
        <div
            class="flex h-24 shrink-0 items-center gap-4 border-b border-bo-border px-6"
            :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
        >
            <img
                :src="logoMark"
                alt="Гангстерс Суши"
                class="aspect-square shrink-0"
                :class="collapsed ? 'h-12 w-12 lg:h-14 lg:w-14' : 'h-12 w-12'"
            />
            <img
                src="/images/logo_text.svg"
                alt=""
                class="h-11 w-auto min-w-0"
                :class="collapsed ? 'lg:hidden' : ''"
            />
        </div>

        <p
            class="px-6 pb-2 pt-4 text-[11px] font-medium uppercase tracking-[0.18em] text-bo-muted/70"
            :class="collapsed ? 'lg:hidden' : ''"
        >
            Меню
        </p>

        <nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-3 pb-6">
            <RouterLink
                v-for="item in session.navItems"
                :key="item.routeName"
                :to="{ name: item.routeName }"
                :title="item.label"
                class="group relative flex h-11 items-center gap-3 px-3 text-sm text-bo-muted transition-colors duration-150 hover:bg-white/[0.04] hover:text-bo-fg"
                :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                active-class="!bg-bo-accent-soft !text-bo-fg before:absolute before:inset-y-2 before:left-0 before:w-0.5 before:bg-bo-accent"
            >
                <ShellIcon
                    :name="item.icon"
                    class="h-5 w-5 shrink-0 transition-colors group-hover:text-bo-fg"
                />
                <span class="truncate" :class="collapsed ? 'lg:hidden' : ''">
                    {{ item.label }}
                </span>
            </RouterLink>
        </nav>
    </aside>
</template>
