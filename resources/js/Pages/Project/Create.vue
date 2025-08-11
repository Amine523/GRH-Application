<template>
  <AuthenticatedLayout title="Create New Project">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Create New Project
        </h2>
        <Link 
          :href="route('projects.index')" 
          class="text-sm text-gray-600 hover:text-gray-900"
        >
          Back to Projects
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="space-y-6">
              <!-- Project Name -->
              <div>
                <InputLabel for="name" value="Project Name" />
                <TextInput
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                />
                <InputError :message="form.errors.name" class="mt-2" />
              </div>

              <!-- Project Members -->
              <div>
                <InputLabel value="Project Members" />
                <div class="mt-2 space-y-2">
                  <div v-for="(member, index) in form.members" :key="index" class="flex items-center space-x-2">
                    <select 
                      v-model="member.id"
                      class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                      :class="{ 'border-red-500': form.errors[`members.${index}.id`] }"
                    >
                      <option value="">Select a project member</option>
                      <option 
                        v-for="user in availableUsers" 
                        :key="user.id" 
                        :value="user.id"
                        :disabled="isUserSelected(user.id, index)"
                      >
                        {{ user.profile?.first_name + ' ' + user.profile?.last_name || user.email }}
                      </option>
                    </select>
                    <button 
                      type="button"
                      @click="removeMember(index)"
                      class="text-red-600 hover:text-red-800"
                      v-if="form.members.length > 1"
                    >
                      Remove
                    </button>
                    <InputError :message="form.errors[`members.${index}.id`]" class="mt-1" />
                  </div>
                </div>
                
                <button 
                  type="button"
                  @click="addMember"
                  class="mt-2 text-sm text-indigo-600 hover:text-indigo-900"
                >
                  Add another project member
                </button>
              </div>

              <div class="flex items-center justify-end mt-6">
                <Link 
                  :href="route('projects.index')" 
                  class="text-sm text-gray-600 hover:text-gray-900 mr-4"
                >
                  Cancel
                </Link>
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Create Project
                </PrimaryButton>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
  users: {
    type: Array,
    required: true,
    default: () => []
  },
  auth: {
    type: Object,
    required: true
  }
});

const form = useForm({
  name: '',
  members: [{ id: '' }],
  manager_id: props.auth.user.id
});

const availableUsers = computed(() => {
  const selectedIds = form.members.map(m => m.id).filter(id => id !== '');
  return props.users.filter(user => 
    !selectedIds.includes(user.id.toString()) &&
    user.id.toString() !== form.manager_id
  );
});

const isUserSelected = (userId, currentIndex) => {
  return form.members.some((member, index) => 
    member.id === userId.toString() && index !== currentIndex
  );
};

const addMember = () => {
  form.members.push({ id: '' });
};

const removeMember = (index) => {
  form.members.splice(index, 1);
};

const submit = () => {
  form.post(route('projects.store'), {
    onSuccess: () => {
      router.visit(route('projects.index'), {
        only: ['projects'],
        preserveScroll: true,
      });
    },
    onError: (errors) => {
      console.error('Error while creating project:', errors);
    }
  });
};
</script>
