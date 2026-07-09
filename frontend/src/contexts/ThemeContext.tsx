import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import type { Lang } from '../i18n/translations'

type ThemeContextValue = {
  theme: 'light' | 'dark'
  lang: Lang
  setTheme: (theme: 'light' | 'dark') => void
  setLang: (lang: Lang) => void
}

const ThemeContext = createContext<ThemeContextValue | null>(null)

export function ThemeProvider({ children }: { children: React.ReactNode }) {
  const [theme, setThemeState] = useState<'light' | 'dark'>(() => (localStorage.getItem('nexa_theme') as 'light' | 'dark') || 'light')
  const [lang, setLangState] = useState<Lang>(() => (localStorage.getItem('nexa_lang') as Lang) || 'en')

  useEffect(() => {
    document.documentElement.classList.toggle('dark', theme === 'dark')
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr'
    document.documentElement.lang = lang
    localStorage.setItem('nexa_theme', theme)
    localStorage.setItem('nexa_lang', lang)
  }, [theme, lang])

  const value = useMemo(() => ({
    theme,
    lang,
    setTheme: setThemeState,
    setLang: setLangState,
  }), [theme, lang])

  return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>
}

export function useTheme() {
  const context = useContext(ThemeContext)
  if (!context) throw new Error('useTheme must be used within ThemeProvider')
  return context
}