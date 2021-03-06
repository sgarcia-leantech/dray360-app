// export default function dev () {
export default async function dev ({ next, store }) {
  if (process.env.NODE_ENV === 'development') {
    console.log('env is dev')
    return next()
  }
  return next('/not-authorized')
}
// }
