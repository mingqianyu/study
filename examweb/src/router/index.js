import Vue from 'vue'
import Router from 'vue-router'

// in development-env not use lazy-loading, because lazy-loading too many pages will cause webpack hot update too slow. so only in production use lazy-loading;
// detail: https://panjiachen.github.io/vue-element-admin-site/#/lazy-loading

Vue.use(Router)

/* Layout */
import Layout from '../views/layout/Layout'

/**
* hidden: true                   if `hidden:true` will not show in the sidebar(default is false)
* alwaysShow: true               if set true, will always show the root menu, whatever its child routes length
*                                if not set alwaysShow, only more than one route under the children
*                                it will becomes nested mode, otherwise not show the root menu
* redirect: noredirect           if `redirect:noredirect` will no redirect in the breadcrumb
* name:'router-name'             the name is used by <keep-alive> (must set!!!)
* meta : {
    title: 'title'               the name show in submenu and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar,
  }
**/

export const constantRouterMap = [
  { path: '/login', component: () => import('@/views/login/index'), hidden: true },
  // { path: '/choose', component: () => import('@/views/choose/index'), hidden: true },
  { path: '/404', component: () => import('@/views/404'), hidden: true },

  {
    path: '/',
    component: Layout,
    redirect: '/dashboard',
    name: 'Dashboard',
    children: [{
      path: 'dashboard',
      component: () => import('@/views/dashboard/index'),
      meta: { title: '首页', icon: 'index' }
    }]
  },
  // 选择页
  {
    path: '/choose/index',
    name: 'choose',
    component: () => import('@/views/choose/index'),
    hidden: true
    // meta: { title: '学科', icon: 'form' }
  },
  // 班级
  {
    path: '/exam',
    component: Layout,
    hidden: true,
    children: [
      {
        path: 'index',
        name: 'exam',
        component: () => import('@/views/exam/index'),
        meta: { title: '试卷', icon: 'class' }
      }
    ]
  }
  // 学科
  // {
  //   path: '/form',
  //   component: Layout,
  //   children: [
  //     {
  //       path: 'index',
  //       name: 'Form',
  //       component: () => import('@/views/form/index'),
  //       meta: { title: '学科', icon: 'form', role: ['admin'] }
  //     }
  //   ]
  // },
  // 课程分类
  // {
  //   path: '/example',
  //   component: Layout,
  //   redirect: '/example/table',
  //   name: 'Example',
  //   meta: { title: '课程分类', icon: 'example', role: ['admin', 'teacher'] },
  //   children: [
  //     {
  //       //   课程
  //       path: 'table',
  //       name: 'Table',
  //       component: () => import('@/views/table/index'),
  //       meta: { title: '课程', icon: 'table' }
  //     },
  //     {
  //       // 课程阶段
  //       path: 'tree',
  //       name: 'Tree',
  //       component: () => import('@/views/tree/index'),
  //       meta: { title: '课程阶段', icon: 'tree' }
  //     },
  //     {
  //       // 知识点
  //       path: 'knowledge',
  //       name: 'knowledge',
  //       component: () => import('@/views/knowledge/index'),
  //       meta: { title: '知识点', icon: 'knowledge' }
  //     }
  //   ]
  // },
  // 题库
  // {
  //   path: '/testLibrary',
  //   component: Layout,
  //   children: [
  //     {
  //       path: 'index',
  //       name: 'testLibrary',
  //       component: () => import('@/views/testLibrary/index'),
  //       meta: { title: '题库', icon: 'testLibrary', role: ['admin', 'teacher'] }
  //     }
  //   ]
  // },
]

export function addNewRouter(role) {
  let routers = []
  if (role === 'admin') {
    routers = [
      // 班级
      {
        path: '/class',
        component: Layout,
        children: [
          {
            path: 'index',
            name: 'class',
            component: () => import('@/views/class/index'),
            meta: { title: '班级', icon: 'class' }
          }
        ]
      },
      // 学科
      {
        path: '/form',
        component: Layout,
        children: [
          {
            path: 'index',
            name: 'Form',
            component: () => import('@/views/form/index'),
            meta: { title: '学科', icon: 'form', role: ['admin'] }
          }
        ]
      },
      // 课程分类
      {
        path: '/example',
        component: Layout,
        redirect: '/example/table',
        name: 'Example',
        meta: { title: '课程分类', icon: 'example', role: ['admin', 'teacher'] },
        children: [
          {
            //   课程
            path: 'table',
            name: 'Table',
            component: () => import('@/views/table/index'),
            meta: { title: '课程', icon: 'table' }
          },
          {
            // 课程阶段
            path: 'tree',
            name: 'Tree',
            component: () => import('@/views/tree/index'),
            meta: { title: '课程阶段', icon: 'tree' }
          },
          {
            // 知识点
            path: 'knowledge',
            name: 'knowledge',
            component: () => import('@/views/knowledge/index'),
            meta: { title: '知识点', icon: 'knowledge' }
          }
        ]
      },
      // 题库
      {
        path: '/testLibrary',
        component: Layout,
        children: [
          {
            path: 'index',
            name: 'testLibrary',
            component: () => import('@/views/testLibrary/index'),
            meta: { title: '题库', icon: 'testLibrary', role: ['admin', 'teacher'] }
          }
        ]
      },
      { path: '*', redirect: '/404', hidden: true }
    ]
  } else if (role === 'teacher') {
    routers = [
      // 班级
      {
        path: '/class',
        component: Layout,
        children: [
          {
            path: 'index',
            name: 'class',
            component: () => import('@/views/class/index'),
            meta: { title: '班级', icon: 'class' }
          }
        ]
      },
      // 课程分类
      {
        path: '/example',
        component: Layout,
        redirect: '/example/table',
        name: 'Example',
        meta: { title: '课程分类', icon: 'example', role: ['admin', 'teacher'] },
        children: [
          {
            //   课程
            path: 'table',
            name: 'Table',
            component: () => import('@/views/table/index'),
            meta: { title: '课程', icon: 'table' }
          },
          {
            // 课程阶段
            path: 'tree',
            name: 'Tree',
            component: () => import('@/views/tree/index'),
            meta: { title: '课程阶段', icon: 'tree' }
          },
          {
            // 知识点
            path: 'knowledge',
            name: 'knowledge',
            component: () => import('@/views/knowledge/index'),
            meta: { title: '知识点', icon: 'knowledge' }
          }
        ]
      },
      // 题库
      {
        path: '/testLibrary',
        component: Layout,
        children: [
          {
            path: 'index',
            name: 'testLibrary',
            component: () => import('@/views/testLibrary/index'),
            meta: { title: '题库', icon: 'testLibrary', role: ['admin', 'teacher'] }
          }
        ]
      },
      { path: '*', redirect: '/404', hidden: true }
    ]
  } else {
    routers = [
      // 班级
      {
        path: '/class',
        component: Layout,
        children: [
          {
            path: 'index',
            name: 'class',
            component: () => import('@/views/class/index'),
            meta: { title: '班级', icon: 'class' }
          }
        ]
      },
      { path: '*', redirect: '/404', hidden: true }
    ]
  }
  return routers
}
export default new Router({
  mode: process.env.NODE_ENV === 'production' ? 'history' : 'hash', // 后端支持可开
  scrollBehavior: () => ({ y: 0 }),
  routes: constantRouterMap
})
