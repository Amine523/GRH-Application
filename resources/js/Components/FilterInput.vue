<template>
    <div>
        <label :for="id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ label }}</label>
        <input
            type="text"
            :id="id"
            v-model="inputValue"
            @input="updateValue"
            class="block w-full p-2 text-gray-600 border border-gray-200 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500 dark:bg-white dark:border-gray-600 dark:placeholder-gray-400 dark:black dark:focus:ring-blue-500 dark:focus:border-blue-500"
            :placeholder="placeholder"
        />
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';

const props = defineProps({
    id: String,
    label: String,
    placeholder: String,
});

const emit = defineEmits();
const inputValue = ref('');

const updateValue = () => {
    emit('update:modelValue', inputValue.value); // Emit the value to the parent
};
</script>
