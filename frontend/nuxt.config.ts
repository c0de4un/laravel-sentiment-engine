export default defineNuxtConfig({
  devtools: { enabled: true },
  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt'
  ],

  nitro: {
    devProxy: {
      '/api': {
        target: 'http://localhost:80/api',
        changeOrigin: true,
      }
    }
  },

  runtimeConfig: {
    public: {
      apiBase: '/api'
    }
  }
})