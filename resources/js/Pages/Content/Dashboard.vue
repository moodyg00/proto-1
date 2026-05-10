<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { ref } from 'vue';

defineProps({ stats: Object, recentBlogPosts: Array, physicalDesigns: Array, upcomingPublications: Array, quickLinks: Array });

const postView = ref('table');
const designView = ref('card');
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <h2 class="text-2xl font-semibold">Content Dashboard</h2>

      <section class="grid gap-4 md:grid-cols-4 xl:grid-cols-7">
        <div v-for="(value, label) in stats" :key="label" class="rounded-xl bg-white p-4 shadow-sm">
          <p class="text-xs uppercase tracking-wide text-slate-500">{{ label }}</p>
          <p class="mt-2 text-lg font-semibold">{{ value }}</p>
        </div>
      </section>

      <section class="rounded-xl bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="font-semibold">Recent Blog Posts</h3>
          <div class="flex gap-2">
            <button class="rounded-md px-3 py-1.5 text-sm" :class="postView === 'table' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="postView = 'table'">Table</button>
            <button class="rounded-md px-3 py-1.5 text-sm" :class="postView === 'card' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="postView = 'card'">Card</button>
          </div>
        </div>

        <div v-if="postView === 'table'" class="overflow-hidden rounded-lg border border-slate-200">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr><th class="px-3 py-2">Title</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Published</th><th class="px-3 py-2">Views</th></tr></thead>
            <tbody>
              <tr v-for="post in recentBlogPosts" :key="post.id" class="border-t border-slate-200"><td class="px-3 py-2"><a :href="`/content/blog-posts/${post.id}`" class="text-blue-600">{{ post.title }}</a></td><td class="px-3 py-2">{{ post.status }}</td><td class="px-3 py-2">{{ post.published_at || '-' }}</td><td class="px-3 py-2">{{ post.view_count }}</td></tr>
            </tbody>
          </table>
        </div>

        <div v-else class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
          <a v-for="post in recentBlogPosts" :key="post.id" :href="`/content/blog-posts/${post.id}`" class="rounded-lg border border-slate-200 p-3 hover:bg-slate-50"><p class="font-semibold">{{ post.title }}</p><p class="text-sm text-slate-600">{{ post.status }}</p></a>
        </div>
      </section>

      <section class="rounded-xl bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="font-semibold">Physical Designs</h3>
          <div class="flex gap-2">
            <button class="rounded-md px-3 py-1.5 text-sm" :class="designView === 'table' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="designView = 'table'">Table</button>
            <button class="rounded-md px-3 py-1.5 text-sm" :class="designView === 'card' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="designView = 'card'">Card</button>
          </div>
        </div>

        <div v-if="designView === 'table'" class="overflow-hidden rounded-lg border border-slate-200">
          <table class="min-w-full text-sm"><thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr><th class="px-3 py-2">Name</th><th class="px-3 py-2">Type</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Updated</th></tr></thead><tbody><tr v-for="design in physicalDesigns" :key="design.id" class="border-t border-slate-200"><td class="px-3 py-2"><a :href="`/content/physical-designs/${design.id}`" class="text-blue-600">{{ design.name }}</a></td><td class="px-3 py-2">{{ design.design_type }}</td><td class="px-3 py-2">{{ design.status }}</td><td class="px-3 py-2">{{ design.updated_at }}</td></tr></tbody></table>
        </div>

        <div v-else class="grid gap-3 md:grid-cols-2 xl:grid-cols-4"><a v-for="design in physicalDesigns" :key="design.id" :href="`/content/physical-designs/${design.id}`" class="rounded-lg border border-slate-200 p-3 hover:bg-slate-50"><p class="font-semibold">{{ design.name }}</p><p class="text-sm text-slate-600">{{ design.design_type }} · {{ design.status }}</p></a></div>
      </section>

      <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-4 shadow-sm"><h3 class="mb-3 font-semibold">Upcoming Publications</h3><div class="space-y-2 text-sm"><div v-for="item in upcomingPublications" :key="item.id" class="rounded-md border border-slate-200 p-2">{{ item.title }} · {{ item.published_at }}</div><p v-if="upcomingPublications.length === 0" class="text-slate-500">No scheduled content.</p></div></div>
        <div class="rounded-xl bg-white p-4 shadow-sm"><h3 class="mb-3 font-semibold">Quick Links</h3><div class="grid gap-2 md:grid-cols-2"><a v-for="link in quickLinks" :key="link.href" :href="link.href" class="rounded-md border border-slate-200 p-2 text-sm hover:bg-slate-50">{{ link.label }}</a></div></div>
      </section>
    </div>
  </AppLayout>
</template>
