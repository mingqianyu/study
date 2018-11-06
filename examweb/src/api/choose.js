import request from '@/utils/request'

export function chooseLogin(data) {
  return request({
    url: '/choiceRole',
    method: 'post',
    data
  })
}
