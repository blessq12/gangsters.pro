<script setup>
import { Color } from "@tiptap/extension-color";
import Image from "@tiptap/extension-image";
import Link from "@tiptap/extension-link";
import Placeholder from "@tiptap/extension-placeholder";
import { Table } from "@tiptap/extension-table";
import TableCell from "@tiptap/extension-table-cell";
import TableHeader from "@tiptap/extension-table-header";
import TableRow from "@tiptap/extension-table-row";
import TextAlign from "@tiptap/extension-text-align";
import { TextStyle } from "@tiptap/extension-text-style";
import Underline from "@tiptap/extension-underline";
import StarterKit from "@tiptap/starter-kit";
import { EditorContent, useEditor } from "@tiptap/vue-3";
import { computed, onBeforeUnmount, ref, watch } from "vue";
import UiButton from "./UiButton.vue";
import UiInput from "./UiInput.vue";
import UiModal from "./UiModal.vue";

const props = defineProps({
    modelValue: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    placeholder: { type: String, default: "Текст документа…" },
});

const emit = defineEmits(["update:modelValue"]);

const linkModalOpen = ref(false);
const linkUrl = ref("");
const imageModalOpen = ref(false);
const imageUrl = ref("");

const editor = useEditor({
    content: props.modelValue || "",
    editable: !props.disabled,
    extensions: [
        StarterKit.configure({
            heading: { levels: [2, 3] },
        }),
        Underline,
        TextStyle,
        Color,
        TextAlign.configure({
            types: ["heading", "paragraph"],
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                rel: "noopener noreferrer",
                target: "_blank",
            },
        }),
        Image.configure({
            allowBase64: false,
            HTMLAttributes: {
                class: "max-w-full",
            },
        }),
        Table.configure({
            resizable: true,
        }),
        TableRow,
        TableHeader,
        TableCell,
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
    ],
    editorProps: {
        attributes: {
            class: "ui-rich-text-editor__content min-h-64 px-4 py-3 outline-none",
        },
    },
    onUpdate: ({ editor: instance }) => {
        emit("update:modelValue", instance.getHTML());
    },
});

const canUse = computed(() => Boolean(editor.value) && !props.disabled);

watch(
    () => props.modelValue,
    (value) => {
        if (!editor.value) {
            return;
        }
        const next = value || "";
        if (editor.value.getHTML() === next) {
            return;
        }
        editor.value.commands.setContent(next, false);
    },
);

watch(
    () => props.disabled,
    (disabled) => {
        editor.value?.setEditable(!disabled);
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

/**
 * @param {string|Record<string, unknown>} nameOrAttrs
 * @param {Record<string, unknown>} [attributes]
 */
function isActive(nameOrAttrs, attributes) {
    if (!editor.value) {
        return false;
    }
    if (typeof nameOrAttrs === "object") {
        return editor.value.isActive(nameOrAttrs);
    }
    return editor.value.isActive(nameOrAttrs, attributes ?? {});
}

/**
 * @param {() => void} action
 */
function run(action) {
    if (!editor.value || props.disabled) {
        return;
    }
    action();
}

function toggleBold() {
    run(() => editor.value.chain().focus().toggleBold().run());
}

function toggleItalic() {
    run(() => editor.value.chain().focus().toggleItalic().run());
}

function toggleUnderline() {
    run(() => editor.value.chain().focus().toggleUnderline().run());
}

function setParagraph() {
    run(() => editor.value.chain().focus().setParagraph().run());
}

/**
 * @param {2|3} level
 */
function toggleHeading(level) {
    run(() => editor.value.chain().focus().toggleHeading({ level }).run());
}

function toggleBulletList() {
    run(() => editor.value.chain().focus().toggleBulletList().run());
}

function toggleOrderedList() {
    run(() => editor.value.chain().focus().toggleOrderedList().run());
}

function toggleBlockquote() {
    run(() => editor.value.chain().focus().toggleBlockquote().run());
}

function setHorizontalRule() {
    run(() => editor.value.chain().focus().setHorizontalRule().run());
}

/**
 * @param {'left'|'center'|'right'|'justify'} align
 */
function setTextAlign(align) {
    run(() => editor.value.chain().focus().setTextAlign(align).run());
}

function clearFormat() {
    run(() => editor.value.chain().focus().unsetAllMarks().clearNodes().run());
}

/**
 * @param {Event} event
 */
function onColorInput(event) {
    const target = event.target;
    const value = target instanceof HTMLInputElement ? target.value : "";
    if (!value) {
        return;
    }
    run(() => editor.value.chain().focus().setColor(value).run());
}

function openLinkModal() {
    if (!editor.value || props.disabled) {
        return;
    }
    linkUrl.value = editor.value.getAttributes("link").href || "https://";
    linkModalOpen.value = true;
}

function applyLink() {
    const href = String(linkUrl.value || "").trim();
    if (!editor.value) {
        return;
    }
    if (href === "") {
        editor.value.chain().focus().extendMarkRange("link").unsetLink().run();
    } else {
        editor.value.chain().focus().extendMarkRange("link").setLink({ href }).run();
    }
    linkModalOpen.value = false;
}

function unsetLink() {
    run(() => editor.value.chain().focus().extendMarkRange("link").unsetLink().run());
    linkModalOpen.value = false;
}

function openImageModal() {
    if (!editor.value || props.disabled) {
        return;
    }
    imageUrl.value = "https://";
    imageModalOpen.value = true;
}

function applyImage() {
    const src = String(imageUrl.value || "").trim();
    if (!editor.value || src === "" || src === "https://") {
        imageModalOpen.value = false;
        return;
    }
    editor.value.chain().focus().setImage({ src }).run();
    imageModalOpen.value = false;
}

function insertTable() {
    run(() => editor.value.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run());
}

function addColumnAfter() {
    run(() => editor.value.chain().focus().addColumnAfter().run());
}

function deleteColumn() {
    run(() => editor.value.chain().focus().deleteColumn().run());
}

function addRowAfter() {
    run(() => editor.value.chain().focus().addRowAfter().run());
}

function deleteRow() {
    run(() => editor.value.chain().focus().deleteRow().run());
}

function deleteTable() {
    run(() => editor.value.chain().focus().deleteTable().run());
}

/**
 * @param {boolean} active
 */
function toolClass(active) {
    return [
        "inline-flex h-8 min-w-8 items-center justify-center border px-2 text-xs transition-colors",
        active
            ? "border-bo-accent bg-bo-accent/20 text-bo-fg"
            : "border-bo-border bg-bo-field text-bo-muted hover:border-bo-border-strong hover:text-bo-fg",
        props.disabled ? "cursor-not-allowed opacity-40" : "",
    ].join(" ");
}
</script>

<template>
    <div class="border border-bo-border bg-bo-field">
        <div class="flex flex-wrap gap-1 border-b border-bo-border p-2">
            <button type="button" :class="toolClass(isActive('bold'))" :disabled="!canUse" title="Жирный" @click="toggleBold">
                B
            </button>
            <button type="button" :class="toolClass(isActive('italic'))" :disabled="!canUse" title="Курсив" @click="toggleItalic">
                <span class="italic">I</span>
            </button>
            <button type="button" :class="toolClass(isActive('underline'))" :disabled="!canUse" title="Подчёркивание" @click="toggleUnderline">
                <span class="underline">U</span>
            </button>

            <span class="mx-1 w-px self-stretch bg-bo-border" />

            <button type="button" :class="toolClass(isActive('paragraph'))" :disabled="!canUse" title="Абзац" @click="setParagraph">
                P
            </button>
            <button type="button" :class="toolClass(isActive('heading', { level: 2 }))" :disabled="!canUse" title="Заголовок H2" @click="toggleHeading(2)">
                H2
            </button>
            <button type="button" :class="toolClass(isActive('heading', { level: 3 }))" :disabled="!canUse" title="Заголовок H3" @click="toggleHeading(3)">
                H3
            </button>

            <span class="mx-1 w-px self-stretch bg-bo-border" />

            <button type="button" :class="toolClass(isActive('bulletList'))" :disabled="!canUse" title="Маркированный список" @click="toggleBulletList">
                • Список
            </button>
            <button type="button" :class="toolClass(isActive('orderedList'))" :disabled="!canUse" title="Нумерованный список" @click="toggleOrderedList">
                1. Список
            </button>
            <button type="button" :class="toolClass(isActive('blockquote'))" :disabled="!canUse" title="Цитата" @click="toggleBlockquote">
                «»
            </button>
            <button type="button" :class="toolClass(false)" :disabled="!canUse" title="Линия" @click="setHorizontalRule">
                ―
            </button>

            <span class="mx-1 w-px self-stretch bg-bo-border" />

            <button type="button" :class="toolClass(isActive({ textAlign: 'left' }))" :disabled="!canUse" title="По левому краю" @click="setTextAlign('left')">
                ⟸
            </button>
            <button type="button" :class="toolClass(isActive({ textAlign: 'center' }))" :disabled="!canUse" title="По центру" @click="setTextAlign('center')">
                ⇔
            </button>
            <button type="button" :class="toolClass(isActive({ textAlign: 'right' }))" :disabled="!canUse" title="По правому краю" @click="setTextAlign('right')">
                ⟹
            </button>
            <button type="button" :class="toolClass(isActive({ textAlign: 'justify' }))" :disabled="!canUse" title="По ширине" @click="setTextAlign('justify')">
                ≡
            </button>

            <span class="mx-1 w-px self-stretch bg-bo-border" />

            <button type="button" :class="toolClass(isActive('link'))" :disabled="!canUse" title="Ссылка" @click="openLinkModal">
                Ссылка
            </button>
            <button type="button" :class="toolClass(false)" :disabled="!canUse" title="Убрать ссылку" @click="unsetLink">
                ⌫Ссылка
            </button>
            <button type="button" :class="toolClass(false)" :disabled="!canUse" title="Картинка по URL" @click="openImageModal">
                Картинка
            </button>

            <label
                class="inline-flex h-8 items-center gap-1 border border-bo-border bg-bo-field px-2 text-xs text-bo-muted"
                title="Цвет текста"
            >
                Цвет
                <input
                    type="color"
                    class="h-5 w-5 cursor-pointer border-0 bg-transparent p-0"
                    :disabled="!canUse"
                    @input="onColorInput"
                >
            </label>

            <span class="mx-1 w-px self-stretch bg-bo-border" />

            <button type="button" :class="toolClass(false)" :disabled="!canUse" title="Таблица 3×3" @click="insertTable">
                Таблица
            </button>
            <button type="button" :class="toolClass(false)" :disabled="!canUse" title="Столбец +" @click="addColumnAfter">
                Ст+
            </button>
            <button type="button" :class="toolClass(false)" :disabled="!canUse" title="Столбец −" @click="deleteColumn">
                Ст−
            </button>
            <button type="button" :class="toolClass(false)" :disabled="!canUse" title="Строка +" @click="addRowAfter">
                Стр+
            </button>
            <button type="button" :class="toolClass(false)" :disabled="!canUse" title="Строка −" @click="deleteRow">
                Стр−
            </button>
            <button type="button" :class="toolClass(false)" :disabled="!canUse" title="Удалить таблицу" @click="deleteTable">
                ⌫Табл
            </button>

            <span class="mx-1 w-px self-stretch bg-bo-border" />

            <button type="button" :class="toolClass(false)" :disabled="!canUse" title="Очистить формат" @click="clearFormat">
                Очистить
            </button>
        </div>

        <EditorContent
            :editor="editor"
            class="ui-rich-text-editor"
        />
    </div>

    <UiModal
        :open="linkModalOpen"
        title="Ссылка"
        @close="linkModalOpen = false"
    >
        <div class="space-y-4">
            <UiInput
                v-model="linkUrl"
                placeholder="https://"
            />
            <div class="flex flex-wrap gap-2">
                <UiButton @click="applyLink">
                    Применить
                </UiButton>
                <UiButton class="!bg-white/10 !text-bo-fg" @click="unsetLink">
                    Убрать ссылку
                </UiButton>
            </div>
        </div>
    </UiModal>

    <UiModal
        :open="imageModalOpen"
        title="Картинка по URL"
        @close="imageModalOpen = false"
    >
        <div class="space-y-4">
            <UiInput
                v-model="imageUrl"
                placeholder="https://…"
            />
            <UiButton @click="applyImage">
                Вставить
            </UiButton>
        </div>
    </UiModal>
</template>

<style>
.ui-rich-text-editor .ProseMirror {
    min-height: 16rem;
    color: var(--color-bo-fg, #f5f5f5);
}

.ui-rich-text-editor .ProseMirror p {
    margin-bottom: 0.75rem;
}

.ui-rich-text-editor .ProseMirror h2 {
    margin: 1rem 0 0.5rem;
    font-size: 1.25rem;
    font-weight: 600;
}

.ui-rich-text-editor .ProseMirror h3 {
    margin: 0.85rem 0 0.4rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.ui-rich-text-editor .ProseMirror ul {
    margin-bottom: 0.75rem;
    list-style: disc;
    padding-left: 1.25rem;
}

.ui-rich-text-editor .ProseMirror ol {
    margin-bottom: 0.75rem;
    list-style: decimal;
    padding-left: 1.25rem;
}

.ui-rich-text-editor .ProseMirror blockquote {
    margin: 0.75rem 0;
    border-left: 3px solid rgba(255, 255, 255, 0.25);
    padding-left: 0.75rem;
    color: rgba(255, 255, 255, 0.75);
}

.ui-rich-text-editor .ProseMirror hr {
    margin: 1rem 0;
    border: 0;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.ui-rich-text-editor .ProseMirror a {
    color: #f87171;
    text-decoration: underline;
}

.ui-rich-text-editor .ProseMirror img {
    max-width: 100%;
    height: auto;
}

.ui-rich-text-editor .ProseMirror table {
    width: 100%;
    border-collapse: collapse;
    margin: 0.75rem 0;
}

.ui-rich-text-editor .ProseMirror th,
.ui-rich-text-editor .ProseMirror td {
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 0.4rem 0.55rem;
    vertical-align: top;
}

.ui-rich-text-editor .ProseMirror th {
    background: rgba(255, 255, 255, 0.06);
    font-weight: 600;
}

.ui-rich-text-editor .ProseMirror p.is-editor-empty:first-child::before {
    color: rgba(255, 255, 255, 0.35);
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}
</style>
