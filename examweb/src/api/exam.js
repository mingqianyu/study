import request from '@/utils/request'
export function treess(query) {
  return request({
    url: '/getCategoryTreeBySubjectId',
    method: 'get',
    params: query
  })
}
export function examList(query) {
  return request({
    url: '/paperList',
    method: 'get',
    params: query
  })
}

export function getQuestion(query) {
  return request({
    url: '/getPaperQuestions',
    method: 'get',
    params: query
  })
}

export function getSelfPaperResult(query) {
  return request({
    url: '/getSelfPaperResult',
    method: 'get',
    params: query
  })
}
export function startExam(query) {
  return request({
    url: '/startExam',
    method: 'get',
    params: query
  })
}

export function refreshPaper(query) {
  return request({
    url: '/refreshPaper',
    method: 'get',
    params: query
  })
}

export function deleExam(query) {
  return request({
    url: '/deletePaper',
    method: 'get',
    params: query
  })
}

export function addExam(data) {
  return request({
    url: '/addPaper',
    method: 'post',
    data
  })
}
export function changePaperStatus(data) {
  // console.log(data)
  return request({
    url: '/changePaperStatus',
    method: 'post',
    data
  })
}
export function getPaperResults(data) {
  // console.log(data)
  return request({
    url: '/getPaperResults',
    method: 'post',
    data
  })
}

export function submitAnswer(data) {
  // console.log(data)
  return request({
    url: '/submitAnswer',
    method: 'post',
    data
  })
}

export function submitPaper(data) {
  // console.log(data)
  return request({
    url: '/submitPaper',
    method: 'post',
    data
  })
}

export function getReadAnswerQuestion(data) {
  // console.log(data)
  return request({
    url: '/getReadAnswerQuestion',
    method: 'post',
    data
  })
}

export function readAnswerQuestion(data) {
  // console.log(data)
  return request({
    url: '/readAnswerQuestion',
    method: 'post',
    data
  })
}

export function getExercise(data) {
  // console.log(data)
  return request({
    url: '/getExercise',
    method: 'post',
    data
  })
}

export function verifyExercise(data) {
  // console.log(data)
  return request({
    url: '/verifyExercise',
    method: 'post',
    data
  })
}
export function getErrorQuestions(data) {
  // console.log(data)
  return request({
    url: '/getErrorQuestions',
    method: 'post',
    data
  })
}
