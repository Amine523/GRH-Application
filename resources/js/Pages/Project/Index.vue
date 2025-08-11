<template>
    <Head title="Projects" />
  
    <AuthenticatedLayout :auth="auth">
      <!-- Header -->
      <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Projects Management
        </h2>
      </template>
  
      <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  
          <!-- Flash messages -->
          <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 p-3 rounded mb-4">
            {{ $page.props.flash.success }}
          </div>
          <div v-else-if="$page.props.flash?.error" class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4">
            {{ $page.props.flash.error }}
          </div>
  
          <!-- Main box -->
          <div class="bg-white shadow rounded-xl p-6">
            <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
              <div>
                <h3 class="text-lg font-semibold">
                  {{ isAdmin ? 'All Projects' : isProjectManager ? 'My Managed Projects' : 'My Projects' }}
                </h3>
                <p class="text-sm text-gray-500">
                  {{ filteredProjects.length }} project{{ filteredProjects.length !== 1 ? 's' : '' }}
                </p>
              </div>
  
              <PrimaryButton v-if="can.createProject" @click="goToCreate">
                + New Project
              </PrimaryButton>
            </div>
  
            <!-- Projects Table -->
            <div v-if="filteredProjects.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Members</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="project in filteredProjects" :key="project.id">
                    <td class="px-6 py-4">
                      <div class="font-medium">{{ project.name }}</div>
                      <div class="text-sm text-gray-500">
                        {{ project.description?.slice(0, 50) }}{{ project.description?.length > 50 ? '...' : '' }}
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      {{ project.manager?.profile?.first_name }} {{ project.manager?.profile?.last_name }}
                    </td>
                    <td class="px-6 py-4">
                      <span :class="statusClass(project.status)" class="px-2 inline-flex text-xs font-semibold rounded-full">
                        {{ formatStatus(project.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      {{ project.members?.length || 0 }} members
                    </td>
                    <td class="px-6 py-4 flex gap-2">
                      <Link :href="route('projects.show', project.id)" class="text-blue-600 hover:underline">View</Link>
                      <Link
                        v-if="can.editProject || project.manager_id === auth.user?.id"
                        :href="route('projects.edit', project.id)"
                        class="text-yellow-600 hover:underline"
                      >Edit</Link>
                      <button
                        v-if="(isAdmin || project.manager_id === auth.user?.id) && can.deleteProject"
                        @click="askDelete(project)"
                        class="text-red-600 hover:underline"
                      >Delete</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
  
            <!-- No projects -->
            <div v-else class="text-center py-12">
              <p class="text-gray-500">No projects yet.</p>
              <!-- <PrimaryButton v-if="can.createProject" @click="goToCreate" class="mt-4">
                + New Project
              </PrimaryButton> -->
            </div>
          </div>
        </div>
      </div>
  
      <!-- Delete Confirmation -->
      <ConfirmationModal :show="showDelete" @close="showDelete = false">
        <template #title>Delete Project</template>
        <template #content>This action cannot be undone. Are you sure?</template>
        <template #footer>
          <SecondaryButton @click="showDelete = false">Cancel</SecondaryButton>
          <DangerButton class="ml-3" :disabled="processing" @click="deleteProject">
            Delete
          </DangerButton>
        </template>
      </ConfirmationModal>
    </AuthenticatedLayout>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue'
  import { Head, Link, router } from '@inertiajs/vue3'
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import SecondaryButton from '@/Components/SecondaryButton.vue'
  import DangerButton from '@/Components/DangerButton.vue'
  import ConfirmationModal from '@/Components/ConfirmationModal.vue'
  
  const props = defineProps({
    projects: { type: Array, default: () => [] },
    auth: { type: Object, required: true },
    can: { type: Object, default: () => ({ createProject: false, editProject: false, deleteProject: false }) },
    isAdmin: { type: Boolean, default: false },
    isProjectManager: { type: Boolean, default: false }
  })
  
  const showDelete = ref(false)
  const projectToDelete = ref(null)
  const processing = ref(false)
  
  const formatStatus = (status) => {
    if (!status) return 'Not Set'
    return status.split('_').map(w => w[0].toUpperCase() + w.slice(1)).join(' ')
  }
  
  const statusClass = (status) => {
    const colors = {
      not_started: 'bg-gray-100 text-gray-800',
      in_progress: 'bg-blue-100 text-blue-800',
      completed: 'bg-green-100 text-green-800',
      on_hold: 'bg-yellow-100 text-yellow-800',
      cancelled: 'bg-red-100 text-red-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
  }
  
  const filteredProjects = computed(() => {
    if (props.isAdmin) return props.projects
    if (props.isProjectManager) return props.projects.filter(p => p.manager_id === props.auth.user?.id)
    return props.projects.filter(p => p.members?.some(m => m.id === props.auth.user?.id))
  })
  
  const goToCreate = () => {
    router.get(route('projects.create'))
  }
  
  const askDelete = (project) => {
    projectToDelete.value = project
    showDelete.value = true
  }
  
  const deleteProject = () => {
    if (!projectToDelete.value) return
    processing.value = true
    router.delete(route('projects.destroy', projectToDelete.value.id), {
      preserveScroll: true,
      onFinish: () => processing.value = false,
      onSuccess: () => {
        showDelete.value = false
        projectToDelete.value = null
      }
    })
  }
  </script>
  