import request from '@/utils/request'
export function courseList(query) {
  return request({
    url: '/courseStageList',
    method: 'get',
    params: query
  })
}

export function addCourse(data) {
  return request({
    url: '/addCourseStage',
    method: 'post',
    data
  })
}
export function editCourse(data) {
  // console.log(data)
  return request({
    url: '/editCourseStage',
    method: 'post',
    data
  })
}
export function deletesto(data) {
  return request({
    url: '/deleteCourseStage',
    method: 'post',
    data
  })
}
