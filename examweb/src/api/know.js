import request from '@/utils/request'
export function knowList(query) {
  return request({
    url: '/knowledgeList',
    method: 'get',
    params: query
  })
}
export function validSubjects() {
  return request({
    url: '/validSubjects',
    method: 'get'
  })
}
export function addKnow(data) {
  return request({
    url: '/addKnowledge',
    method: 'post',
    data
  })
}
export function editknow(data) {
  // console.log(data)
  return request({
    url: '/editKnowledge',
    method: 'post',
    data
  })
}
export function shanchu(data) {
  return request({
    url: '/deleteKnowledge',
    method: 'post',
    data
  })
}
