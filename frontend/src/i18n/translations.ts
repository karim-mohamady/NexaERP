export type Lang = 'en' | 'ar'

const dictionary = {
  en: {
    dashboard: 'Dashboard',
    search: 'Search',
    create: 'Create',
    save: 'Save',
    cancel: 'Cancel',
    logout: 'Logout',
  },
  ar: {
    dashboard: 'لوحة التحكم',
    search: 'بحث',
    create: 'إضافة',
    save: 'حفظ',
    cancel: 'إلغاء',
    logout: 'تسجيل الخروج',
  },
} satisfies Record<Lang, Record<string, string>>

export function t(lang: Lang, key: string) {
  return (dictionary[lang] as Record<string, string>)[key] ?? key
}
