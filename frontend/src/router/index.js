import Vue from 'vue'
import VueRouter from 'vue-router'
import Orders from '@/views/Orders'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'Orders',
    component: Orders
  }
]

const router = new VueRouter({
  mode: 'history',
  routes
})

export default router
