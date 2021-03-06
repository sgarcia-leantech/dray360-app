/* eslint-disable camelcase */
import { mapState } from 'vuex'
import { has_permissions, has_permission } from '@/utils/has_permissions'
import { hasRole } from '@/utils/has_role'
import auth from '@/store/modules/auth'

export default {

  computed: {
    ...mapState(auth.moduleName, {
      currentUser: state => state.currentUser
    })
  },

  methods: {
    hasRole (role) {
      return hasRole(this.currentUser, role)
    },

    hasRoles (roles) {
      return hasRole(this.currentUser, roles)
    },

    hasPermissions (...requestedPermissions) {
      return has_permissions(this.currentUser, ...requestedPermissions)
    },

    hasPermission (requestedPermission) {
      return has_permission(this.currentUser, requestedPermission)
    },

    isSuperadmin () {
      return this.currentUser !== undefined && this.currentUser.is_superadmin
    },

    canViewOtherCompanies () {
      return has_permission(this.currentUser, 'all-companies-view')
    }
  }
}
