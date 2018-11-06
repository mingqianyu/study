import request from '@/utils/request'
export function subjectList() {
  return request({
    url: '/subjectList',
    method: 'get'
  })
}
export function valsubjectList() {
  return request({
    url: '/validSubjects',
    method: 'get'
  })
}
export function addubject(data) {
  return request({
    url: '/addSubject',
    method: 'post',
    data
  })
}
export function editSubject(data) {
  // console.log(data)
  return request({
    url: '/editSubject',
    method: 'post',
    data
  })
}
export function shanchu(data) {
  return request({
    url: '/deleteSubject',
    method: 'post',
    data
  })
}
