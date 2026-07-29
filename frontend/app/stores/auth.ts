import { defineStore } from 'pinia'

interface User {
    id: number
    name: string
    email: string
    role: string
}

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null)
    const isAuthenticated = computed(() => !!user.value)

    function setUser(userData: User) {
        user.value = userData
    }

    async function fetchUser() {
        try {
            // useRequestHeaders прокидывает куки браузера в SSR-запрос на бэкенд
            const headers = useRequestHeaders(['cookie'])

            const response = await $fetch<User>('/api/auth/me', {
                headers: headers.cookie ? { cookie: headers.cookie } : {}
            })
            user.value = response
        } catch (error) {
            user.value = null
        }
    }

    function logout() {
        user.value = null
        navigateTo('/auth')
    }

    return { user, isAuthenticated, setUser, fetchUser, logout }
})