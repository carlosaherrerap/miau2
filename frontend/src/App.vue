<template>
  <div class="app-root">
    <Navbar v-if="auth.isAuthenticated" />
    <main class="main-content">
      <router-view v-slot="{ Component }">
        <transition name="page-fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Navbar from '@/components/Navbar.vue'

const auth = useAuthStore()

onMounted(() => {
  if (auth.token) {
    auth.fetchUser()
  }
})
</script>

<style>
.app-root {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #eef2f6;
}
</style>
