import Cookies from 'js-cookie'

const TokenKey = 'Admin-Token'

export function getToken() {
  return Cookies.get(TokenKey)
}

export function setToken(token) {
  return Cookies.set(TokenKey, token)
}

export function removeToken() {
  return Cookies.remove(TokenKey)
}

const DataKey = 'Admin-Data'

export function getData() {
  return Cookies.get(DataKey)
}

export function setData(data) {
  return Cookies.set(DataKey, data)
}

export function removeData() {
  return Cookies.remove(DataKey)
}

const shenKey = 'Admin-newShenfen'

export function getNewShenfen() {
  return Cookies.get(shenKey)
}

export function setNewShenfen(data) {
  return Cookies.set(shenKey, data)
}

// 选择角色后登录请求成功的数据
const choseKey = 'Admin-choose'

export function getchoose() {
  return Cookies.get(choseKey)
}

export function setchoose(data) {
  return Cookies.set(choseKey, data)
}

