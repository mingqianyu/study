import router from './router'
// import store from './store'
import NProgress from 'nprogress' // Progress 进度条
import 'nprogress/nprogress.css'// Progress 进度条样式
// import { Message } from 'element-ui'
import { getToken } from '@/utils/auth' // 验权
import { addNewRouter, constantRouterMap } from '@/router'

// // permissiom judge function
// function hasPermission(roles, permissionRoles) {
//   if (roles.indexOf('admin') >= 0) return true // admin permission passed directly
//   if (!permissionRoles) return true
//   return roles.some(role => permissionRoles.indexOf(role) >= 0)
// }

// const whiteList = ['/login'] // 不重定向白名单
router.beforeEach((to, from, next) => {
  NProgress.start()
  // 判断是否有getToken
  if (getToken()) {
    if (router.options.routes.length === 5) {
      const newRouters = addNewRouter(localStorage.getItem('role'))
      router.addRoutes(newRouters)
      router.options.routes = constantRouterMap.concat(newRouters)
      // next({ path: to.redirectedFrom })
      next({ ...to, replace: true })
    }
    if (to.path === '/login') {
      next({ path: '/' })
      NProgress.done()
    } else {
      // 按原有跳转
      next()
      NProgress.done()
    }
  } else {
    if (to.path === '/login') {
      // 如果是login页面就登录
      next()
      NProgress.done()
    } else {
      // 没有token,全部重定向到登录页面
      next(`/login?redirect=${to.path}`) // 否则全部重定向到登录页
      NProgress.done()
    }
  }
  // if (getToken()) {
  //   // 如果有token
  //   if (to.path === '/login') {
  //     next({ path: '/' })
  //     NProgress.done() // if current page is dashboard will not trigger	afterEach hook, so manually handle it
  //   } else {
  //     if (store.getters.roles.length === 0) {
  //       store.dispatch('GetInfo').then(res => { // 拉取用户信息
  //         next()
  //       }).catch((err) => {
  //         store.dispatch('FedLogOut').then(() => {
  //           Message.error(err || '登录失效,请重新登录.')
  //           next({ path: '/' })
  //         })
  //       })
  //     } else {
  //       next()
  //     }
  //   }
  // } else {
  //   // 没有token
  //   if (whiteList.indexOf(to.path) !== -1) {
  //     next()
  //   } else {
  //     next(`/login?redirect=${to.path}`) // 否则全部重定向到登录页
  //     NProgress.done()
  //   }
  // }
})

router.afterEach(() => {
  NProgress.done() // 结束Progress
})
