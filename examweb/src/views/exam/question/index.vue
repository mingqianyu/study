<template>
  <div style="padding-left: 50px;padding-right: 50px;">
    <div style="font-size: 16px;">
      <div><span style="font-size: 16px;margin-right: 10px;font-weight: bold;">{{ titlenumber }}: </span>{{ question.title | unescape }} <span style="color: #E6A23C;">{{ question.isInterview === 0 ? '': '（面试题）' }}</span></div>
      <div style="margin-top: 10px;margin-left: 7px;">
        <el-radio-group v-for="(item, index) in question.options.split('[++++]')" v-model="data" :key="index">
          <el-radio :label="index" :style="{ color : (index === (answerstr.charCodeAt(0) - 65) ? '#F56C6C' : '#303133') }" disabled style="margin-left: 20px;">{{ item | unescape }}</el-radio>
        </el-radio-group>
      </div>
      <div>
        <p>参考答案: <span style="color: #67C23A">{{ question.answer | unescape }}</span></p>
      </div>
      <div>
        <p>答案解析: <span style="color: #409EFF;">{{ question.analysis | unescape }}</span></p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Index',
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
  },
  computed: {
    data: function() {
      return parseInt(this.answerstr.charCodeAt(0) - 65)
    }
  }
}

</script>

<style scoped>

</style>
