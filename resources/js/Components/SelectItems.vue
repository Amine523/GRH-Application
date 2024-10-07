<template>
    <div>

        <select
            :id="id"
            v-model="internalModelValue"
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
            <option disabled value="" selected>Click to select</option>
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>

        <InputError class="mt-2" :message="error" />
    </div>
</template>

<script setup>
import InputError from "@/Components/InputError.vue";
import {defineEmits} from "../../../.vite/deps/chunk-JSUVVRKD.js";
import {ref, watch} from "vue";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    options: {
        type: Array,
        required: true,
    },
    modelValue: {
        type: String,
        required: true,
    },
    error: {
        type: String,
        default: '',
    }
});

const emit = defineEmits(['update:modelValue']);
const internalModelValue = ref(props.modelValue);

watch(internalModelValue, (newValue) => {
    emit('update:modelValue', newValue);
});
</script>
