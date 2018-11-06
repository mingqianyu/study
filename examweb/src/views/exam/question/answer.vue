<template>
  <!--判断题-->
  <div style="font-size: 16px;padding: 0 50px 0 50px;">
    <div><span style="font-size: 16px;margin-right: 10px;font-weight: bold;">{{ titlenumber }}: </span>{{ question.title | unescape }}<span style="color: #E6A23C;">{{ question.isInterview === 0 ? '': '（面试题）' }}</span></div>
    <div style="margin-top: 10px;margin-left: 27px;">
      <el-input
        v-model="answerstr"
        disabled
        autosize
        type="textarea"
        placeholder="请输入内容"
        style="width: 400px;">
        <!---->
      </el-input>
    </div>
    <div>
      <p>参考答案: <span style="color: #67C23A">{{ question.answer | unescape }}</span></p>
    </div>
    <div>
      <p>答案解析: <span style="color: #409EFF;">{{ question.analysis | unescape }}</span></p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Answer',
  props: {
    question: {
      type: Object,
      required: true
    },
    answerstr: {
      type: String,
      required: true
    },
    titlenumber: {
      type: Number,
      required: true
    }
  }
}
</script>

<style scoped>

</style>
