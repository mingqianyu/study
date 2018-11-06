<template>
  <div>
    <el-table
      :data="studentdetails"
      border
      style="width: 100%;margin-left: 0%">
      <el-table-column
        prop="name"
        label="学生名称"
        align="center"
        width="150">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="status"
        label="试卷状态"
        align="center">
        <template slot-scope="scope">
          <span>{{ resultStatusF(scope.row.paperResult) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="objective_score"
        label="选择判断得分"
        align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.paperResult.objective_score }}</span>
        </template>
      </el-table-column>
      <el-table-column
        v-if="answers"
        prop="objective_score"
        label="简答题得分"
        align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.paperResult.subjective_score }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="objective_score"
        label="总分"
        align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.paperResult.objective_score ? (scope.row.paperResult.subjective_score ? (scope.row.paperResult.objective_score + scope.row.paperResult.subjective_score) : scope.row.paperResult.objective_score) : (scope.row.paperResult.subjective_score ? scope.row.paperResult.subjective_score : '0') }}</span>
        </template>
      </el-table-column>
      <el-table-column v-if="answers" label="操作" align="center" width="240" fixed="right">
        <template slot-scope="scope" >
          <el-button :disabled="returnReadStatus(scope.row.paperResult).disable" type="warning" size="small" style="margin-top: 5px;margin-right: 11px" plain @click="piyue(scope.row)">{{ returnReadStatus(scope.row.paperResult).operationName }}</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog
      :visible.sync="answerArrShow"
      title="试卷详情"
      width="80%"
      append-to-body
      type="index"
      style="min-width: 1100px;">
      <el-table
        :data="answerArr"
        border>
        <el-table-column
          type="index"
          width="30">
          <!---->
        </el-table-column>
        <el-table-column
          prop="title"
          label="简答题目"
          align="center"
          width="240">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="title"
          label="参考答案"
          align="center">
          <template slot-scope="scope">
            <span>{{ scope.row.answer }}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="title"
          label="学生答案"
          align="center">
          <template slot-scope="scope">
            <span>{{ scope.row.studentAnswer }}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="title"
          label="满分分数"
          align="center"
          width="50">
          <template slot-scope="scope">
            <span>{{ scope.row.ruleAnswerScore }}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="title"
          label="学生得分"
          align="center"
          width="250">
          <template slot-scope="scope">
            <el-input-number v-model="answerScores[scope.$index]" controls-position="right" style="width: 150px;" >
              <!---->
            </el-input-number>
          </template>
        </el-table-column>
      </el-table>
      <div style="display: flex;justify-content: space-between;padding: 0 50px 0 50px;">
        <div>
          <!---->
        </div>
        <el-button type="primary" plain style="margin-top: 20px;" @click="readAnswer">提交分数</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { getReadAnswerQuestion, readAnswerQuestion } from '../../../api/exam'
export default {
  name: 'Examdetails',
  props: {
    answers: {
      type: Boolean,
      required: true
    },
    studentdetails: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      answerArr: [],
      answerArrShow: false,
      answerScores: [],
      answerSco: 1,
      studentId: null,
      studentAnswerScores: 0
    }
  },
  methods: {
    readAnswer() {
      const readAnswerList = {}
      readAnswerList.answerScores = this.answerScores
      readAnswerList.paperId = parseInt(localStorage.getItem('paperId'))
      readAnswerList.studentId = this.studentId
      readAnswerQuestion(readAnswerList).then((result) => {
        // console.log('提交成功')
        this.$emit('regetPaperResults')
        this.answerArrShow = false
        // this.studentAnswerScores = 0
        // for (let i = 0; i < this.answerScores.length; i++) {
        //   this.studentAnswerScores += this.answerScores[i]
        // }
        // for (let j = 0; j < this.studentdetails.length; j++) {
        //   if (this.studentdetails[j].id === this.studentId) {
        //     this.studentdetails[j].paperResult.subjective_score = this.studentAnswerScores
        //     this.studentdetails[j].paperResult.status = 3
        //   }
        // }
      })
    },
    piyue(row) {
      const getReadList = {}
      getReadList.studentId = row.id
      this.studentId = row.id
      getReadList.paperId = parseInt(localStorage.getItem('paperId'))
      getReadAnswerQuestion(getReadList).then((result) => {
        this.answerArr = result.data
        this.answerArrShow = true
        for (let i = 0; i < this.answerArr.length; i++) {
          if (this.answerArr[i].subjective_score > 0) {
            this.answerArr[i].answersBolen = false
          }
        }
      })
    },
    resultStatusF: function(paperResult) {
      if (Array.isArray(paperResult)) {
        return '未答题'
      } else {
        if (paperResult.status === 0) {
          return '未答题'
        } else if (paperResult.status === 1) {
          return '答题中'
        } else if (paperResult.status === 2) {
          return '已交卷'
        } else {
          return '批阅完成'
        }
      }
    },
    returnReadStatus: function(paperResult) {
      let status = 0
      if (Array.isArray(paperResult)) {
        status = 0
      } else {
        status = paperResult.status
      }
      return { disable: (status !== 2), operationName: status < 3 ? '批阅' : '已批阅' }
    }
  }
}
</script>

<style scoped>

</style>
