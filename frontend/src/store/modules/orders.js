import { reqStatus } from '@/enums/req_status'
import { getOrders, getOrderDetail, updateOrderDetail, getDownloadPDFURL } from '@/store/api_calls/orders'

export const types = {
  setOrders: 'SET_ORDERS',
  setCurrentOrder: 'SET_CURRENT_ORDER',
  getOrders: 'GET_ORDERS',
  getOrderDetail: 'GET_ORDER_DETAIL',
  updateOrderDetail: 'UPDATE_ORDER_DETAIL',
  getDownloadPDFURL: 'GET_DOWNLOAD_PDF',
  setReloadRequests: 'SET_RELOAD_REQUESTS'
}

const initialState = {
  list: [],
  links: {},
  meta: {},
  currentOrder: {},
  reloadRequests: false
}

const mutations = {
  [types.setOrders] (state, { data, links, meta }) {
    state.list = data.map(item => {
      item.key = `${item.id}-${item.order.id || null}`
      return item
    })
    state.links = links
    state.meta = meta
  },
  [types.setCurrentOrder] (state, orderData) {
    state.currentOrder = orderData
  },
  [types.setReloadRequests] (state, reload) {
    state.reloadRequests = reload
  }
}

const actions = {
  async [types.getOrders] ({ commit }, filters) {
    const query = filters['filter[query]']
    const dateQuery = filters['filter[created_between]']
    const filtersForParams = { ...filters }
    delete filtersForParams.query
    delete filtersForParams.dateQuery

    const [error, data] = await getOrders(filtersForParams, query, dateQuery)

    if (error) return reqStatus.error

    commit(types.setOrders, data)
    return reqStatus.success
  },

  async [types.getOrderDetail] ({ commit }, order) {
    const [error, data] = await getOrderDetail(order)

    if (error) return reqStatus.error

    commit(types.setCurrentOrder, data)
    return reqStatus.success
  },

  async [types.updateOrderDetail] ({ commit, state }, { id, changes }) {
    const [error, data] = await updateOrderDetail({ id, changes })
    let newOrder = {}

    if (!error) {
      delete data.ocr_data
      newOrder = { ...state.currentOrder, ...data, ...changes }
    } else {
      newOrder = { ...state.currentOrder, ...changes }
    }

    commit(types.setCurrentOrder, newOrder)

    if (error) return reqStatus.error
    return reqStatus.success
  },

  async [types.getDownloadPDFURL] ({ commit }, orderId) {
    const [error, data] = await getDownloadPDFURL(orderId)

    if (error) return { status: reqStatus.error, data: error.response.data }

    return { status: reqStatus.success, data: data }
  },
  [types.setReloadRequests] ({ commit }, reload) {
    commit(types.setReloadRequests, reload)
  }
}

export default {
  moduleName: 'ORDERS',
  namespaced: true,
  state: initialState,
  mutations,
  actions
}
