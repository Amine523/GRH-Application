<template>
  <select
    :id="id"
    ref="input"
    v-bind="$attrs"
    class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
    :class="{ 'border-red-500': error }"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
  >
    <option v-if="showDefaultOption" value="">Select an option</option>
    <option
      v-for="(option, index) in options"
      :key="index"
      :value="option.value"
      :disabled="option.disabled"
    >
      {{ option.label }}
    </option>
  </select>
  <div v-if="error" class="text-red-600 text-sm mt-1">
    {{ error }}
  </div>
</template>

<script>
export default {
  name: 'SelectInput',
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default: null,
    },
    modelValue: {
      type: [String, Number, Boolean],
      default: '',
    },
    options: {
      type: Array,
      required: true,
      validator: (value) => {
        return value.every(option => 'value' in option && 'label' in option);
      },
    },
    error: {
      type: String,
      default: null,
    },
    showDefaultOption: {
      type: Boolean,
      default: true,
    },
  },
  emits: ['update:modelValue'],
  methods: {
    focus() {
      this.$refs.input.focus();
    },
  },
};
</script>
