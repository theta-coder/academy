import { ref, watch } from 'vue'

// Module-level state persists across page navigations in the SPA
const isDark = ref(false)

export function useDarkMode() {
    const applyDark = (dark) => {
        if (dark) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
        try {
            localStorage.setItem('darkMode', dark ? 'dark' : 'light')
        } catch {}
    }

    const toggle = () => {
        isDark.value = !isDark.value
    }

    const init = () => {
        let saved = null
        try {
            saved = localStorage.getItem('darkMode')
        } catch {}
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
        isDark.value = saved ? saved === 'dark' : prefersDark
        applyDark(isDark.value)
    }

    watch(isDark, applyDark)

    return { isDark, toggle, init }
}
