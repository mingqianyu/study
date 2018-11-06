import request from '@/utils/request'

export function getClassList(userId, className) {
  const data = {
    userId,
    className
  }
  return request({
    url: '/classList',
    method: 'post',
    data
  })
}

