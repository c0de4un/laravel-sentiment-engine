<template>
  <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-2xl">

      <!-- Шапка -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">AI Sentiment Engine</h1>
        <div v-if="authStore.isAuthenticated" class="flex items-center space-x-4">
          <span class="text-sm text-gray-600">{{ authStore.user?.email }}</span>
          <button @click="authStore.logout()" class="text-sm text-indigo-600 hover:underline">
            Выйти
          </button>
        </div>
        <div v-else>
          <button @click="navigateTo('/auth')" class="text-sm text-indigo-600 hover:underline">
            Войти
          </button>
        </div>
      </div>

      <!-- Если не авторизован -->
      <div v-if="!authStore.isAuthenticated" class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 text-center">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Доступ закрыт</h2>
        <p class="text-gray-500 mb-6">Пожалуйста, авторизуйтесь, чтобы анализировать тексты.</p>
        <button @click="navigateTo('/auth')" class="inline-flex justify-center py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
          Перейти к авторизации
        </button>
      </div>

      <!-- Если авторизован -->
      <div v-else class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-gray-200 space-y-6">

        <!-- Результат анализа (появляется сверху) -->
        <div v-if="analysisResult" class="p-4 rounded-lg border flex items-center justify-between" :class="resultBgClass">
          <div>
            <p class="text-sm font-medium text-gray-500">Тональность:</p>
            <p class="text-xl font-bold capitalize" :class="resultTextClass">
              {{ analysisResult.sentiment === 'unknown' ? 'Не удалось определить' : analysisResult.sentiment }}
            </p>
          </div>
          <button @click="analysisResult = null" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <!-- Форма ввода -->
        <div>
          <label for="text" class="block text-sm font-medium text-gray-700 mb-2">
            Текст для анализа
          </label>
          <textarea
              id="text"
              v-model="text"
              rows="5"
              :disabled="isAnalyzing"
              placeholder="Например: Этот новый смартфон просто ужасен, батарея держится полдня..."
              class="w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-50 disabled:cursor-not-allowed transition-colors"
          ></textarea>
          <p v-if="errorMessage" class="text-red-500 text-xs mt-2">{{ errorMessage }}</p>
        </div>

        <!-- Кнопка отправки / Прелоадер -->
        <div class="flex justify-end">
          <button
              @click="submitAnalysis"
              :disabled="isAnalyzing || text.trim().length < 3"
              class="inline-flex items-center justify-center py-2.5 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors w-full sm:w-auto"
          >
            <svg v-if="isAnalyzing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isAnalyzing ? 'Анализирую...' : 'Анализировать' }}</span>
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

const text = ref('')
const isAnalyzing = ref(false)
const analysisResult = ref<{ sentiment: string | null } | null>(null)
const errorMessage = ref<string | null>(null)

// Вычисляемые классы для цветовой индикации результата
const resultBgClass = computed(() => {
  if (!analysisResult.value?.sentiment) return 'bg-gray-50 border-gray-200'
  switch (analysisResult.value.sentiment) {
    case 'positive': return 'bg-green-50 border-green-200'
    case 'negative': return 'bg-red-50 border-red-200'
    case 'neutral': return 'bg-blue-50 border-blue-200'
    default: return 'bg-gray-50 border-gray-200'
  }
})

const resultTextClass = computed(() => {
  if (!analysisResult.value?.sentiment) return 'text-gray-700'
  switch (analysisResult.value.sentiment) {
    case 'positive': return 'text-green-600'
    case 'negative': return 'text-red-600'
    case 'neutral': return 'text-blue-600'
    default: return 'text-gray-700'
  }
})

const submitAnalysis = async () => {
  if (text.value.trim().length < 3) return

  isAnalyzing.value = true
  analysisResult.value = null
  errorMessage.value = null

  try {
    // 1. Отправляем текст в очередь (получаем 202 Accepted и ID)
    const response = await $fetch('/api/analyze', {
      method: 'POST',
      body: { text: text.value },
      credentials: 'include'
    })

    const analysisId = response.data.id

    // 2. Запускаем поллинг (опрос статуса каждые 2 секунды)
    await pollAnalysis(analysisId)

  } catch (error: any) {
    errorMessage.value = error.data?.message || 'Ошибка отправки текста на анализ'
    isAnalyzing.value = false
  }
}

const pollAnalysis = (id: number) => {
  const interval = setInterval(async () => {
    try {
      const res = await $fetch(`/api/analyze/${id}`, {
        headers: {
          'Accept': 'application/json' // <--- Добавили заголовок
        },
        credentials: 'include' // <--- Чтобы кука точно улетела
      })

      if (res.data.status === 'completed' || res.data.status === 'failed') {
        clearInterval(interval)
        analysisResult.value = res.data
        isAnalyzing.value = false

        if (res.data.status === 'failed') {
          errorMessage.value = 'Сервис не смог обработать текст. Попробуйте позже.'
        }
      }
    } catch (error) {
      clearInterval(interval)
      errorMessage.value = 'Потеряно соединение с сервером при проверке статуса'
      isAnalyzing.value = false
    }
  }, 2000)
}
</script>