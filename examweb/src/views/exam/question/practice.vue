<template>
  <div>
    <el-button type="primary" plain @click="chooseF">选择出题范围</el-button>
    <el-dialog
      :visible.sync="xuanSHow"
      title="选择考试范围"
      width="50%"
      style="min-width: 1100px;">
      <el-tree
        ref="tree"
        :data="zuidashu"
        :props="defaultProps"
        show-checkbox
        node-key="value">
        <!--kl-->
      </el-tree>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="getList">确 定</el-button>
      </span>
    </el-dialog>
    <div style="margin-top: 30px;height: 300px;width: 800px;">
      <!--选择题-->
      <div v-if="choiceShow" style="margin-left: 150px;max-width: 800px;">
        <p><span style="color: #303133; font-weight: bold;margin-right: 10px;">题目类型 :</span>{{ question.isInterview === 0 ? '类型题' : '面试题' }}</p>
        <p><span style="color: #303133; font-weight: bold;margin-right: 10px;">题目 :</span>{{ question.title | unescape }}</p>
        <el-radio-group v-model="studentChoose" @change="cosoleF">
          <div v-for="(item, index) in options" :key="index" style="display: inline;padding-left: 50px;padding-top: 20px;">
            <el-radio :label="String.fromCharCode(index +65)" style="margin-right: 15px;">{{ item | unescape }}</el-radio>
          </div>
        </el-radio-group>
        <div v-if="analysisShow">
          <p :style="{ color : (analysisObj.isCorrect === true ? '#67C23A' : '#F56C6C') }">{{ analysisObj.isCorrect === true ? '恭喜你,答对了!!!' : '答案错误,再接再厉!' }}</p>
          <p>答案: <span style="color: #67C23A;">{{ analysisObj.answer }}</span></p>
          <p>解析 : <span style="color: #606266">{{ analysisObj.analysis | unescape }}</span></p>
        </div>
      </div>
      <!--判断题-->
      <div v-if="judgeShow" style="margin-left: 150px;max-width: 800px;">
        <p><span style="color: #303133; font-weight: bold;margin-right: 10px;">题目类型 :</span>{{ question.isInterview === 0 ? '类型题' : '面试题' }}</p>
        <p><span style="color: #303133; font-weight: bold;margin-right: 10px;">题目 :</span>{{ question.title | unescape }}</p>
        <el-radio-group v-model="studentJudege" style="margin-left: 50px;" @change="cosoleF">
          <el-radio label="T" style="margin-right: 15px;">正确</el-radio>
          <el-radio label="F" style="margin-right: 15px;">错误</el-radio>
        </el-radio-group>
        <div v-if="analysisShow">
          <p :style="{ color : (analysisObj.isCorrect === true ? '#67C23A' : '#F56C6C') }">{{ analysisObj.isCorrect === true ? '恭喜你,答对了!!!' : '答案错误,再接再厉!' }}</p>
          <p>答案: <span style="color: #67C23A;">{{ analysisObj.answer }}</span></p>
          <p>解析 : <span style="color: #606266">{{ analysisObj.analysis | unescape }}</span></p>
        </div>
      </div>
      <!--简单题-->
      <div v-if="answerShow" style="margin-left: 150px;max-width: 800px;">
        <p><span style="color: #303133; font-weight: bold;margin-right: 10px;">题目类型 :</span>{{ question.isInterview === 0 ? '类型题' : '面试题' }}</p>
        <p><span style="color: #303133; font-weight: bold;margin-right: 10px;">题目 :</span>{{ question.title | unescape }}</p>
        <el-input
          v-model="answerstudent"
          :autosize="{ minRows: 9, maxRows: 20}"
          type="textarea"
          style="width: 500px;height: 240px;"
          placeholder="请输入内容">
          <!--sdf-->
        </el-input>
        <div v-if="analysisShow">
          <p>答案: <span style="color: #67C23A;">{{ analysisObj.answer }}</span></p>
          <p>解析 : <span style="color: #606266">{{ analysisObj.analysis | unescape }}</span></p>
        </div>
      </div>
    </div>
    <div v-show="sonShow" style="margin-top: 50px; overflow: hidden; padding-right: 300px;">
      <el-button size="mini" type="success" plain style="float: right;margin-left: 10px;" @click="nextF">下一题</el-button>
      <el-button size="mini" type="primary" plain style="float: right;" @click="sumitPracticeAnswer">提交</el-button>
    </div>
  </div>
</template>

<script>
import { getExercise, verifyExercise } from '../../../api/exam'

export default {
  name: 'Practice',
  props: {
    zuidashu: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
    //  ,m,
      defaultProps: {
        children: 'children',
        label: 'label'
      },
      xuanSHow: false,
      sonShow: false,
      analysisShow: false,
      parcticeList: {
        category: {
          courseIds: [],
          courseStageIds: [],
          knowledgeIds: []
        }
      },
      answerstudent: '',
      checkBoxId: [],
      question: {},
      options: [],
      studentChoose: undefined,
      choiceShow: false,
      judgeShow: false,
      answerShow: false,
      studentJudege: '',
      sumitList: {
        exerciseId: null,
        answer: '',
        analysisObj: {}
      }
    }
  },
  created() {
  // kjl
  },
  mounted() {
    // this.getList()
  },
  methods: {
  //  asfd
    chooseF() {
      this.xuanSHow = true
      var _this = this
      setTimeout(function() {
        _this.$refs.tree.setCheckedKeys([])
      }, 100)
    },
    sumitPracticeAnswer() {
      if (this.question.type === 'choice') {
        this.sumitList.answer = this.studentChoose
        this.subF()
      } else if (this.question.type === 'judge') {
        this.sumitList.answer = this.studentJudege
        this.subF()
      } else if (this.question.type === 'answer') {
        this.sumitList.answer = this.answerstudent
        this.subF()
      }
    },
    subF() {
      console.log(this.sumitList)
      verifyExercise(this.sumitList).then((result) => {
        console.log(result.data)
        this.analysisShow = true
        this.analysisObj = result.data
      })
    },
    nextF() {
      this.getList()
    },
    cosoleF() {
    },
    getList() {
      this.options = []
      this.studentChoose = undefined
      this.exerciseId = null
      // this.question = {}
      this.analysisShow = false
      this.xuanSHow = false
      this.checkBoxId = this.$refs.tree.getCheckedKeys()
      for (let i = 0; i < this.checkBoxId.length; i++) {
        if (this.checkBoxId[i].substring(0, 2) === 'cu') {
          this.parcticeList.category.courseIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else if (this.checkBoxId[i].substring(0, 2) === 'cs') {
          this.parcticeList.category.courseStageIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else if (this.checkBoxId[i].substring(0, 2) === 'kn') {
          this.parcticeList.category.knowledgeIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        }
      }
      this.checkBoxId = this.$refs.tree.getHalfCheckedKeys()
      for (let i = 0; i < this.checkBoxId.length; i++) {
        if (this.checkBoxId[i].substring(0, 2) === 'cu') {
          this.parcticeList.category.courseIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else if (this.checkBoxId[i].substring(0, 2) === 'cs') {
          this.parcticeList.category.courseStageIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else if (this.checkBoxId[i].substring(0, 2) === 'kn') {
          this.parcticeList.category.knowledgeIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        }
      }
      getExercise(this.parcticeList).then((result) => {
        this.sonShow = true
        this.sumitList.exerciseId = parseInt(result.data.exerciseId)
        this.question = result.data.question
        if (this.question.type === 'choice') {
          this.options = this.question.options.split('[++++]')
          this.choiceShow = true
          this.judgeShow = false
          this.answerShow = false
        } else if (this.question.type === 'judge') {
          this.choiceShow = false
          this.judgeShow = true
          this.answerShow = false
        } else if (this.question.type === 'answer') {
          this.choiceShow = false
          this.judgeShow = false
          this.answerShow = true
        }
      })
    }
  }
}
</script>

<style scoped>

</style>
