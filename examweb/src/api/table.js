import request from '@/utils/request'
export function courseListss(query) {
  return request({
    url: '/courseList',
    method: 'get',
    params: query
  })
}
export function valsubjectList() {
  return request({
    url: '/validSubjects',
    method: 'get'
  })
}
export function addcourses(data) {
  return request({
    url: '/addCourse',
    method: 'post',
    data
  })
}
export function editCourses(data) {
  return request({
    url: '/editCourse',
    method: 'post',
    data
  })
}
export function deleteCourses(data) {
  return request({
    url: '/deleteCourse',
    method: 'post',
    data
  })
}
