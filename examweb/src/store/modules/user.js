import { login, logout, getInfo } from '@/api/login'
import { getToken, setToken, removeToken, setData, setNewShenfen, setchoose } from '@/utils/auth'

const user = {
  state: {
    token: getToken(),
    name: '',
    avatar: '',
    roles: []
  },

  mutations: {
    SET_TOKEN: (state, token) => {
      state.token = token
    },
    SET_NAME: (state, name) => {
      state.name = name
    },
    SET_AVATAR: (state, avatar) => {
      state.avatar = avatar
    },
    SET_ROLES: (state, roles) => {
      state.roles = roles
    }
  },

  actions: {
    // 登录
    Login({ commit }, userInfo) {
      // const phone = userInfo.phone
      // const password = userInfo.password
      // console.log(phone)
      return new Promise((resolve, reject) => {
        // console.log(phone, password)
        login(userInfo).then(response => {
          const data = response.data
          console.log(data)
          if (data.role === 'admin') {
            localStorage.setItem('id', data.id)
          }
          // console.log(data)
          if (data.institutions) {
            const shenfen = data.institutions
            if (shenfen.length > 1) {
              var newShenfen = []
              for (let i = 0; i < shenfen.length; i++) {
                if (parseInt(shenfen[i].institutions_id) === 1) {
                  newShenfen.push(shenfen[i])
                }
              }
              // console.log(newShenfen)
            }
          }
          setData(data)
          setchoose(data)
          setNewShenfen(data)
          setToken(data.token)
          commit('SET_TOKEN', data.token)
          resolve()
        }).catch(error => {
          reject(error)
        })
      })
    },

    // 获取用户信息
    GetInfo({ commit, state }) {
      return new Promise((resolve, reject) => {
        getInfo(state.token).then(response => {
          const data = response.data
          if (data.roles && data.roles.length > 0) { // 验证返回的roles是否是一个非空数组
            commit('SET_ROLES', data.roles)
          } else {
            reject('getInfo: roles must be a non-null array !')
          }
          commit('SET_NAME', data.name)
          commit('SET_AVATAR', data.avatar)
          resolve(response)
        }).catch(error => {
          reject(error)
        })
      })
    },

    // 登出
    LogOut({ commit, state }) {
      return new Promise((resolve, reject) => {
        logout(state.token).then(() => {
          commit('SET_TOKEN', '')
          // commit('SET_ROLES', [])
          resolve()
        }).catch(error => {
          reject(error)
        })
      })
    },

    // 前端 登出
    FedLogOut({ commit }) {
      return new Promise(resolve => {
        commit('SET_TOKEN', '')
        removeToken()
        resolve()
      })
    }
  }
}

export default user
