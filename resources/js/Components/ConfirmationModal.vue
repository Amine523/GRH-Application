<template>
    <Modal :show="show" @close="close">
        <div class="px-6 py-4">
            <div class="text-lg font-medium text-gray-900">
                <slot name="title">
                    {{ title }}
                </slot>
            </div>

            <div class="mt-4 text-sm text-gray-600">
                <slot name="content">
                    {{ content }}
                </slot>
            </div>
        </div>

        <div class="flex flex-row justify-end px-6 py-4 bg-gray-100 text-right">
            <SecondaryButton class="mr-2" @click="close">
                <slot name="cancel-button">
                    Cancel
                </slot>
            </SecondaryButton>

            <DangerButton
                class="ml-2"
                :class="{ 'opacity-25': processing }"
                :disabled="processing"
                @click="confirm"
            >
                <slot name="confirm-button">
                    Confirm
                </slot>
            </DangerButton>
        </div>
    </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const emit = defineEmits(['confirm', 'close']);

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Are you sure?',
    },
    content: {
        type: String,
        default: 'Are you sure you want to perform this action?',
    },
});

const processing = ref(false);

const confirm = () => {
    processing.value = true;
    emit('confirm');
};

const close = () => {
    if (!processing.value) {
        emit('close');
    }
};
</script>
