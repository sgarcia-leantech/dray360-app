import axios from './axios'

export default function ({ store, router }) {
  axios.ori.interceptors.response.use(
    (response) => (response),
    async (error) => {
      const { status, config, data } = error.response

      if (data.redirect) {
        window.location = `//${data.redirect}`
        return Promise.reject(error)
      }

      if (status === 401 && !['/api/login', '/api/user'].includes(config.url)) {
        store.dispatch('AUTH/simpleLogout')
        router.push('/login')
        return
      } else if (status === 404) {
        router.push('/pagenotfound')
        return
      } else if (status === 503) {
        router.push('/application-downtime')
      }

      return Promise.reject(error)
    }
  )
}
