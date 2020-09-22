import { reqStatus } from '@/enums/req_status'
import { getUsers, deleteUser, editUser, addUser, getRoles, changeUserStatus } from '@/store/api_calls/users'

export const types = {
  setUsers: 'SET_USERS',
  getUsers: 'GET_USERS',
  deleteUser: 'DELETE_USER',
  editUser: 'EDIT_USER',
  addUser: 'ADD_USER',
  getRoles: 'GET_ROLES',
  setRoles: 'SET_ROLES',
  changeUserStatus: 'CHANGE_USER_STATUS'
}

const initialState = {
  users: [],
  roles: []
}

const mutations = {
  [types.setUsers] (state, { usersData }) {
    state.users = usersData
  },

  [types.deleteUser] (state, { id }) {
    state.users.forEach(element => {
      if (element.id === id) {
        state.users.splice(element, 1)
      }
    })
  },

  [types.addUser] (state, { userData }) {
    state.users.push(userData)
  },

  [types.editUser] (state, { userData, i }) {
    state.users[i] = userData
  },

  [types.setRoles] (state, { rolesData }) {
    state.roles = rolesData
  }
}

const actions = {
  async [types.getUsers] ({ commit }) {
    const [error, data] = await getUsers()

    if (error) return reqStatus.error

    commit(types.setUsers, { usersData: data.data })
    return reqStatus.success
  },

  async [types.deleteUser] ({ commit }, id) {
    const [error] = await deleteUser(id)

    if (error) return reqStatus.error

    return reqStatus.success
  },

  async [types.addUser] ({ commit }, user) {
    const [error] = await addUser(user)

    if (error) return reqStatus.error

    commit(types.addUser, { user })
    return reqStatus.success
  },

  async [types.editUser] ({ commit }, user) {
    const userId = user.user_id
    delete user.user_id

    const [error, data] = await editUser(user, userId)

    if (error) return reqStatus.error

    commit(types.editUser, { userData: data.data }, userId)
    return reqStatus.success
  },

  async [types.getRoles] ({ commit }) {
    const [error, data] = await getRoles()

    if (error) return reqStatus.error

    commit(types.setRoles, { rolesData: data.data })
    return reqStatus.success
  },

  async [types.changeUserStatus] ({ commmit }, payload) {
    const [error] = await changeUserStatus(payload.userId, payload.newStatus)

    if (error) return reqStatus.error

    return reqStatus.success
  }
}

export default {
  moduleName: 'USER_DASHBOARD',
  namespaced: true,
  state: initialState,
  mutations,
  actions
}
