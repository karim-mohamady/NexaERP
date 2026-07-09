import { useAuth } from '../contexts/AuthContext'

export function usePermissions() {
  const { permissions, user } = useAuth()
  const roles = user?.roles?.map((role) => role.name) ?? []
  const isSuper = roles.includes('Super Admin') || roles.includes('Admin')

  function can(permission: string) {
    return isSuper || permissions.includes(permission)
  }

  function canAny(list: string[]) {
    return isSuper || list.some((permission) => permissions.includes(permission))
  }

  return { can, canAny, roles, isSuper }
}
