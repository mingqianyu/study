import request from '@/utils/request'
export function getAllCategoryTree() {
  return request({
    url: '/getAllCategoryTree',
    method: 'get'
  })
}
export function questionAllList(query) {
  return request({
    url: '/questionList',
    method: 'get',
    params: query
  })
}
export function addquestion(data) {
  return request({
    url: '/addQuestion',
    method: 'post',
    data
  })
}
export function editQuest(data) {
  return request({
    url: '/editQuestion',
    method: 'post',
    data
  })
}
export function deleteques(data) {
  return request({
    url: '/deleteQuestion',
    method: 'post',
    data
  })
}

export function importQuestion(data) {
  return request({
    url: '/importQuestion',
    method: 'post',
    data
  })
}
