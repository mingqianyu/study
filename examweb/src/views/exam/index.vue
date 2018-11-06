<template>
  <div class="dashboard-container">
    <el-tabs type="border-card">
      <el-tab-pane label="试卷">
        <el-input
          v-model="pageList.pageName"
          placeholder="请输入试卷名称,回车进行搜索"
          clearable
          style="width: 240px;"
          @change="seachWay">
          <!---->
        </el-input>
        <el-button v-if="addExamShow" type="primary" plain style="margin-bottom: 10px;" @click="examDialog" >添加试卷</el-button>
        <el-table
          :data="examAllList"
          border
          style="width: 100%;margin-left: 0%;margin-top: 10px;">
          <el-table-column
            type="index"
            align="center"
            width="50">
            <!--/-->
          </el-table-column>
          <el-table-column
            prop="title"
            label="试卷名称"
            align="center"
            width="260">
            <template slot-scope="scope">
              <span>{{ scope.row.title }}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="createName"
            label="创建人"
            align="center"
            width="150">
            <template slot-scope="scope">
              <span>{{ scope.row.createName }}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="create_time"
            label="创建时间"
            align="center"
            width="200">
            <template slot-scope="scope">
              <span>{{ scope.row.create_time | parseTime }}</span>
            </template>
          </el-table-column>
          <el-table-column
            v-if="addExamShow"
            prop="status"
            label="试卷状态"
            align="center">
            <template slot-scope="scope">
              <span>{{ scope.row.status === 0 ? '未发布' : '已发布' }}</span>
            </template>
          </el-table-column>
          <el-table-column
            v-else
            prop="status"
            label="试卷状态"
            align="center">
            <template slot-scope="scope">
              <span>{{ resultStatusF(scope.row) }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" align="center" width="240" fixed="right">
            <template slot-scope="scope" >
              <el-button v-if="addExamShow" type="primary" size="small" plain @click="lookExam(scope.row)">查看试卷</el-button>
              <el-button v-else type="primary" size="small" plain @click="studentlookExam(scope.row)">查看试卷</el-button>
              <el-button v-if="addExamShow" style="width: 80px" type="success" size="small" plain @click="changeStatus(scope.row)">{{ publicF(scope.row) }}</el-button>
              <el-button v-else v-show="startExamShowF(scope.row)" type="primary" size="small" plain @click="beginExam(scope.row)">开始考试</el-button>
              <el-button v-if="addExamShow" type="primary" size="small" style="margin-top: 5px;" plain @click="detailExam(scope.row)">考试详情</el-button>
              <el-button v-if="addExamShow" type="warning" size="small" style="margin-top: 5px;margin-right: 11px; width: 80px;" plain @click="deleteExam(scope.row.id)">{{ '删除' }}</el-button>
            </template>
          </el-table-column>
        </el-table>
        <div class="block" style="margin-top: 15px;">
          <el-pagination
            :page-sizes="[5, 10, 20, 50]"
            :page-size="5"
            :total="totalcount"
            layout="total, sizes, prev, pager, next, jumper"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange">
            <!--{{ ming }}-->
          </el-pagination>
        </div>
        <el-dialog
          :visible.sync="examShow"
          title="添加试卷"
          width="60%"
          style="min-width: 1100px;">
          <el-form :model="form" label-width="100px">
            <el-form-item label="试卷题目" label-width="100px" style="width: 600px;">
              <el-input v-model="form.title" prop="title" style="width: 400px;">
                <!--sd-->
              </el-input>
            </el-form-item>
            <el-form-item label="试卷考点" label-width="100px" style="width: 600px;">
              <el-tree
                ref="tree"
                :data="zuidashu"
                :props="defaultProps"
                show-checkbox
                node-key="value">
                <!--kl-->
              </el-tree>
            </el-form-item>
            <el-form-item label="选择题" label-width="100px" style="width: 1100px;">
              <span>困难: </span>
              <el-input-number v-model="form.rule.choice.hard" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
              <span style="display: inline-block;margin-left: 10px;">中等: </span>
              <el-input-number v-model="form.rule.choice.middle" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
              <span style="display: inline-block;margin-left: 10px;">简单: </span>
              <el-input-number v-model="form.rule.choice.easy" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
              <span style="display: inline-block;margin-left: 10px;">分数: </span>
              <el-input-number v-model="form.rule.choice.score" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
            </el-form-item>
            <el-form-item label="判断题" label-width="100px" style="width: 1100px;">
              <span>困难: </span>
              <el-input-number v-model="form.rule.judge.hard" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
              <span style="display: inline-block;margin-left: 10px;">中等: </span>
              <el-input-number v-model="form.rule.judge.middle" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
              <span style="display: inline-block;margin-left: 10px;">简单: </span>
              <el-input-number v-model="form.rule.judge.easy" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
              <span style="display: inline-block;margin-left: 10px;">分数: </span>
              <el-input-number v-model="form.rule.judge.score" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
            </el-form-item>
            <el-form-item label="简答题" label-width="100px" style="width: 1100px;">
              <span>困难: </span>
              <el-input-number v-model="form.rule.answer.hard" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
              <span style="display: inline-block;margin-left: 10px;">中等: </span>
              <el-input-number v-model="form.rule.answer.middle" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
              <span style="display: inline-block;margin-left: 10px;">简单: </span>
              <el-input-number v-model="form.rule.answer.easy" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
              <span style="display: inline-block;margin-left: 10px;">分数: </span>
              <el-input-number v-model="form.rule.answer.score" :min="0" :max="100" size="small" controls-position="right" style="width: 100px;" @change="handleChange">
                <!---->
              </el-input-number>
            </el-form-item>
            <el-form-item label="考试总分" label-width="100px" style="width: 600px;">
              <span style="color:#F56C6C ">满分100分!</span>
            </el-form-item>
            <el-form-item label="考试时间" label-width="100px" style="width: 600px;">
              <el-input v-model="second" prop="title" style="width: 100px;">
                <!--sd-->
              </el-input>
              分钟
            </el-form-item>
          </el-form>
          <span slot="footer" class="dialog-footer">
            <el-button @click="examShow = false">取 消</el-button>
            <el-button type="primary" @click="send">确 定</el-button>
          </span>
        </el-dialog>
        <el-dialog
          :visible.sync="paperShow"
          :title="paperTitle"
          width="90%"
          style="min-width: 1100px;">
          <el-button type="primary" style="margin-top: -15px;margin-bottom: 10px;" @click="resetExam">刷新试卷</el-button>
          <el-table
            :data="questionValList"
            border
            style="width: 100%;margin-left: 0%">
            <el-table-column
              label="题号"
              type="index"
              align="center"
              width="50">
              <!---->
            </el-table-column>
            <el-table-column
              prop="title"
              label="题目名称"
              align="center">
              <template slot-scope="scope">
                <span>{{ scope.row.title | unescape }}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="type"
              align="center"
              label="题目类型"
              width="100">
              <template slot-scope="scope">
                <span>{{ typeF(scope.row) }}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="type"
              align="center"
              label="面试题"
              width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.isInterview === 0 ? '否' : '是' }}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="options"
              label="题目选项">
              <template slot-scope="scope">
                <span>{{ optionsF(scope.row) | unescape }}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="answer"
              label="题目答案"
              min-width="200">
              <template slot-scope="scope">
                <span>{{ scope.row.answer | unescape }}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="analysis"
              label="题目解析">
              <template slot-scope="scope">
                <span>{{ scope.row.analysis | unescape }}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="level"
              label="题目难度"
              align="center"
              width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.level }}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="knowledgeName"
              label="涉及知识点">
              <template slot-scope="scope">
                <span>{{ scope.row.knowledgeName }}</span>
              </template>
            </el-table-column>
          </el-table>
        </el-dialog>
        <el-dialog
          :visible.sync="studentExamListShow"
          title="答题页面"
          width="70%"
          style="min-width: 1000px;"
          min-width="1200px"
          @close="closedialog">
          <div style="display: flex;justify-content: left;margin-bottom: 20px;min-width: 600px;max-width: 1000px;flex-wrap: wrap;margin: 0 auto;">
            <div v-for="(item, index) in studentExamList" :key="item.id" style="margin-left: .5%;margin-top: .5%;">
              <el-button :style="{ background: (examQuestionIndex === index ? '#409EFF' : (studentExamList[index].answerValue === '' ? '' : '#67C23A')), color: (examQuestionIndex === index ? '#fff' : (studentExamList[index].answerValue === '' ? '#409EFF' : '#fff'))}" class="question-number" type="primary" plain @click="everyItem(index)">{{ index+1 }}</el-button>
            </div>
          </div>
          <hr>
          <div style="margin: 30px 200px 50px 200px;display: flex;flex-direction: column;align-items: center">
            <div style="height: 300px;">
              <p style="font-size: 16px;font-weight: bold;line-height: 26px;">题目: <span>{{ showQuestionList.title | unescape }}</span> <span style="color: #F56C6C;">{{ showQuestionList.isInterview === 0 ? '' : '(面试题)' }}</span></p>
              <el-radio-group v-model="showQuestionList.answerValue" style="margin-top: 5px;">
                <div v-if="choiceShow" style="max-width: 1000px;justify-content: space-between;flex-wrap: wrap">
                  <p>题目选项:</p>
                  <div v-for="(item,index) in studentChoiceOption" :key="item" style="margin-top: 5%;">
                    <el-radio :label="String.fromCharCode(65 + parseInt(index))" style="margin-left: 10px;font-size: 16px;">{{ item | unescape }}</el-radio>
                  </div>
                </div>
                <div v-if="judgeShow">
                  <span>选择:</span>
                  <el-radio-group v-model="showQuestionList.answerValue" >
                    <el-radio label="T">正确</el-radio>
                    <el-radio label="F">错误</el-radio>
                  </el-radio-group>
                </div>
                <div v-if="answerShow" style="display: flex;justify-content: space-between">
                  <span>答案:</span>
                  <el-input
                    v-model="showQuestionList.answerValue"
                    :rows="5"
                    :autosize="{ minRows: 9, maxRows: 100}"
                    type="textarea"
                    style="width: 400px;"
                    placeholder="请输入内容">
                    {{ ming }}
                  </el-input>
                </div>
              </el-radio-group>
            </div>
            <div style="display: flex;margin-top: 40px;justify-content: space-between;padding: 0 100px 0 100px;">
              <el-button type="primary" size="mini" plain @click="upQuestion">上一题</el-button>
              <div style="padding-left: 200px;">
                <el-button v type="success" size="mini" plain @click="nextQuestion">下一题</el-button>
              </div>
            </div>
          </div>
          <div style="display: flex;justify-content: space-between; align-items: center;padding: 0 50px 0 50px ;">
            <p style="font-size: 20px">剩余考试时间
              <span style="color: #F56C6C;margin: 0 10px 0 10px">
                {{ Math.floor(surplerTime/60) }} : {{ surplerTime % 60 }}
              </span>
            </p>
            <el-button type="success" style="height: 40px;font-size: 16px;" @click="submitPaperF">提交考试</el-button>
          </div>
        </el-dialog>
        <el-dialog
          :visible.sync="sExamshow"
          title="试卷详情"
          width="60%"
          style="min-width: 1100px;">
          <div style="margin: 0 auto;width: 500px;text-align: center;font-size: 20px;font-weight: bold;margin-top: -30px;">
            <p>{{ examtitle }}</p>
          </div>
          <div style="width: 100%;text-align: right;font-size: 16px;padding-right: 150px;">
            <p style="text-decoration: blink;padding-bottom: 5px;">分数: <span style="color: #F56C6C; font-weight: bold;font-size: 18px">{{ totalScore }}分</span></p>
          </div>
          <!--选择题模板-->
          <div v-for="(item, index) in studentChoiceList" :key="item.id">
            <question-view :question="item" :answerstr="studentChoiceAnswerList[index]" :titlenumber="index+1">{{ ming }}</question-view>
          </div>
          <div v-for="(item, index) in studentJudgeList" :key="item.id">
            <judge-view :question="item" :answerstr="studentJudgeAnswerList[index]" :titlenumber="index+studentChoiceList.length+1">{{ ming }}</judge-view>
          </div>
          <div v-for="(item, index) in studentAnswerList1" :key="item.id">
            <answer :question="item" :answerstr="studentAnswerAnswerList[index]" :titlenumber="index+studentChoiceList.length+1+studentJudgeList.length">{{ ming }}</answer>
          </div>
        </el-dialog>
        <el-dialog
          :visible.sync="scoreDialog"
          title="分数"
          width="30%"
          style="min-width: 1000px;">
          <p style="font-size: 16px;">本次你的判断题,选择题的总分为 <span style="color: #F56C6C;font-weight: bold;">{{ studentScore }} 分</span></p>
          <span slot="footer" class="dialog-footer">
            <el-button type="primary" @click="scoreDialog = false">确 定</el-button>
          </span>
        </el-dialog>
        <el-dialog
          :visible.sync="examdetailsShow"
          title="考试详情"
          width="70%"
          style="min-width: 1000px;">
          <examdetails :studentdetails="studentdetails" :answers="answerstr" @regetPaperResults="paperResults">{{ ming }}</examdetails>
          <span slot="footer" class="dialog-footer">
            <el-button type="primary" @click="examdetailsShow = false">确 定</el-button>
          </span>
        </el-dialog>
      </el-tab-pane>
      <el-tab-pane v-if="!addExamShow" label="练习">
        <div style="height: 500px;">
          <practice :zuidashu="zuidashu">
            <!---->
          </practice>
        </div>
      </el-tab-pane>
      <el-tab-pane v-if="!addExamShow" label="错题集">
        <errorlist :zuidashu="zuidashu">
          <!--k-->
        </errorlist>
        <div style="height: 500px;">
          {{ ming }}
        </div>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import { treess, submitPaper, submitAnswer, startExam, addExam, examList, getQuestion, deleExam, refreshPaper, changePaperStatus, getPaperResults, getSelfPaperResult } from '../../api/exam'
import { parseTime } from '../../filters'
import questionView from './question/index'
import judgeView from './question/judge'
import answer from './question/answer'
import examdetails from './question/examdetails'
import practice from './question/practice'
import errorlist from './question/errorlist'

export default {
  name: 'Dashboard',
  components: { questionView, judgeView, answer, examdetails, practice, errorlist },
  data() {
    return {
      role: '',
      examName: '',
      examdetailsShow: false,
      scoreDialog: false,
      examtitle: '',
      studentdetails: [],
      answerstr: true,
      totalScore: null,
      studentChoice: null,
      studentJudge: '',
      studentAnswer: '',
      innerVisible: false,
      addExamShow: true,
      examShow: false,
      paperShow: false,
      sExamshow: false,
      studentAnswerList: '',
      ming: '',
      checkBoxId: [],
      questionValList: [],
      studentChoiceOption: [],
      answerList: {
        title: '',
        radio: ''
      },
      form: {
        title: '',
        classId: null,
        papernum: '你好',
        rule: {
          courseIds: [],
          courseStageIds: [],
          knowledgeIds: [],
          choice: {
            count: null,
            hard: null,
            middle: null,
            easy: null,
            score: null
          },
          judge: {
            count: null,
            hard: null,
            middle: null,
            easy: null,
            score: null
          },
          answer: {
            count: null,
            hard: null,
            middle: null,
            easy: null,
            score: null
          },
          totalCount: null,
          totalscore: 100
        },
        time: null,
        isNeedRead: 0
      },
      subjectAllList: [],
      subjectStorgeAllList: [],
      knowAllList: [],
      zuidashu: [],
      defaultProps: {
        children: 'children',
        label: 'label'
      },
      second: null,
      examAllList: [],
      examId: {
        id: null
      },
      totalcount: null,
      delId: {
        id: null
      },
      pageList: {
        classId: null,
        page: 1,
        pageSize: 5,
        pageName: undefined
      },
      paperTitle: '',
      choiceOptions: [],
      choiceVal: {
        'A': ''
      },
      startExamShow: false,
      studentExamListShow: false,
      studentExamList: [],
      showQuestionList: {},
      surplerTime: null,
      choiceShow: false,
      judgeShow: false,
      answerShow: false,
      studentShow: false,
      examQuestionIndex: 0,
      submitList: {
        paperId: null,
        questionId: null,
        answer: undefined
      },
      submitPaperList: {
        paperId: null
      },
      studentAnswers: [],
      questionObj: {},
      questionString: '',
      studentChoiceList: [],
      studentChoiceAnswerList: [],
      studentJudgeList: [],
      studentJudgeAnswerList: [],
      studentAnswerList1: [],
      studentAnswerAnswerList: [],
      studentScore: null,
      timeid1: null
    }
  },
  computed: {
    ...mapGetters([
      'name',
      'roles'
    ]),
    submitAns: function() {
      return this.showQuestionList.answerValue
    }
  },
  watch: {
    submitAns: function(newValue, oldValue) {
      this.submitF()
    }
  },
  created() {
    this.role = localStorage.getItem('role')
    if (this.role === 'student') {
      this.addExamShow = false
    }
    const subjectIdObj = {}
    subjectIdObj.subjectId = localStorage.getItem('subjectId')
    treess(subjectIdObj).then((result) => {
      this.zuidashu = result.data
    }).catch(() => {})
    this.getList()
  },
  methods: {
    closedialog() {
      clearInterval(this.timeid1)
    },
    itemF(item, index) {
      if (this.questionValList[index].type === 'choice' || this.questionValList[index].type === 'judge') {
        if (index === 3) {
          // console.log(this.questionValList[index].answer)
          // console.log(item)
          // console.log(this.questionValList[index].answer === item)
        }
        if (this.questionValList[index].answer === item) {
          return '正确'
        } else {
          return '错误'
        }
      } else {
        return '等待批阅'
      }
    },
    seachWay() {
      this.getList()
    },
    submitPaperF() {
      submitPaper(this.submitPaperList).then((result) => {
        // console.log(result.data.objectiveScore)
        this.studentScore = result.data.objectiveScore
        this.studentExamListShow = false
        this.scoreDialog = true
        this.getList()
      })
    },
    submitF() {
      // console.log('提交函数')
      if (this.showQuestionList.answerValue !== '') {
        this.submitList.answer = this.showQuestionList.answerValue
        if (this.submitList.answer !== '') {
          submitAnswer(this.submitList).then(() => {
            // console.log('已提交')
          })
        }
      }
    },
    nextQuestion() {
      if (this.examQuestionIndex < this.studentExamList.length - 1) {
        this.examQuestionIndex = this.examQuestionIndex + 1
        this.everyItem(this.examQuestionIndex)
      }
    },
    upQuestion() {
      if (this.examQuestionIndex > 0) {
        this.examQuestionIndex = this.examQuestionIndex - 1
        this.everyItem(this.examQuestionIndex)
      }
    },
    everyItem(index) {
      // 所有题目
      // console.log(this.studentExamList)
      this.innerVisible = true
      this.showQuestionList = this.studentExamList[index]
      this.submitList.questionId = this.showQuestionList.id
      this.examQuestionIndex = index
      // console.log(this.showQuestionList)
      if (this.showQuestionList.type === 'choice') {
        this.studentChoice = this.showQuestionList.answerValue
        this.studentChoiceOption = this.showQuestionList.options.split('[++++]')
        this.choiceShow = true
        this.judgeShow = false
        this.answerShow = false
      } else if (this.showQuestionList.type === 'judge') {
        this.studentJudge = this.showQuestionList.answerValue
        this.judgeShow = true
        this.choiceShow = false
        this.answerShow = false
      } else if (this.showQuestionList.type === 'answer') {
        this.studentAnswer = this.showQuestionList.answerValue
        this.judgeShow = false
        this.choiceShow = false
        this.answerShow = true
      }
    },
    resetExam() {
      this.$confirm('此操作将刷新试卷, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        refreshPaper(this.examId).then((result) => {
          this.$message({
            type: 'success',
            message: '刷新成功!'
          })
          this.questionValList = result.data.questions
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消刷新'
        })
      })
    },
    optionsF(row) {
      return row.options.split('[++++]').join(',')
    },
    typeF(row) {
      if (row.type === 'choice') {
        return '选择题'
      } else if (row.type === 'judge') {
        return '判断题'
      } else {
        return '简答题'
      }
    },
    handleSizeChange(val) {
      this.pageList.pageSize = val
      this.getList()
    },
    handleCurrentChange(val) {
      this.pageList.page = val
      this.getList()
    },
    lookExam(row) {
      this.examId.id = row.id
      this.paperTitle = row.title
      // console.log(this.examId)
      getQuestion(this.examId).then((resule) => {
        // console.log(resule.data)
        this.questionValList = resule.data.questions
        this.paperShow = true
      }).catch(() => {})
    },
    beginExam(row) {
      const beginId = {}
      beginId.paperId = row.id
      this.submitList.paperId = row.id
      this.submitPaperList.paperId = row.id
      startExam(beginId).then((result) => {
        // console.log(result.data)
        this.studentExamList = result.data.questions
        this.surplerTime = parseInt(result.data.countDownTime)
        this.studentExamListShow = true
        this.TimeF()
        this.everyItem(0)
      }).catch((error) => {
        // console.log(error.response.data.code)
        if (error.response.data.code === 1804) {
          this.$message({
            message: '考试时间已用尽! 系统将帮你自动交卷 ! ',
            type: 'warning'
          })
          this.submitPaperF()
        }
      })
    },
    startExamShowF(row) {
      if (row.resultStatus === 0) {
        return true
      } else if (row.resultStatus === 1) {
        return true
      } else {
        return false
      }
    },
    studentlookExam(row) {
      const getSelf = {}
      this.examtitle = row.title
      getSelf.paperId = row.id
      getSelfPaperResult(getSelf).then((result) => {
        // console.log(result.data)
        // 清空
        this.studentChoiceList = []
        this.studentJudgeList = []
        this.studentAnswerList1 = []
        this.studentChoiceAnswerList = []
        this.studentJudgeAnswerList = []
        this.studentAnswerAnswerList = []
        this.questionValList = result.data.questions
        this.studentAnswers = result.data.answers
        this.totalScore = result.data.totalScore
        for (let i = 0; i < this.questionValList.length; i++) {
          if (this.questionValList[i].type === 'choice') {
            this.studentChoiceList.push(this.questionValList[i])
            this.studentChoiceAnswerList.push(this.studentAnswers[i])
          } else if (this.questionValList[i].type === 'judge') {
            this.studentJudgeList.push(this.questionValList[i])
            this.studentJudgeAnswerList.push(this.studentAnswers[i])
          } else if (this.questionValList[i].type === 'answer') {
            this.studentAnswerList1.push(this.questionValList[i])
            this.studentAnswerAnswerList.push(this.studentAnswers[i])
          }
        }
        // console.log(this.studentAnswers)
        // console.log(this.studentChoiceAnswerList)
        // console.log(this.studentJudgeAnswerList)
        // console.log(this.studentAnswerAnswerList)
        this.sExamshow = true
      })
    },
    deleteExam(id) {
    //
      this.delId.id = id
      this.$confirm('此操作将永久删除该试卷, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        deleExam(this.delId).then(() => {
          this.$message({
            type: 'success',
            message: '删除成功!'
          })
          this.getList()
        }).catch(() => {
          this.$notify({ title: '失败', message: '修改知识点失败！', type: 'error', duration: 2000 })
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },
    paperResults() {
      const paperId = parseInt(localStorage.getItem('paperId'))
      getPaperResults({ paperId: paperId }).then((result) => {
        this.studentdetails = result.data
        this.examdetailsShow = true
      })
    },
    detailExam(row) {
      const getResults = {}
      getResults.paperId = row.id
      // console.log(row)
      if (row.is_need_read === 0) {
        this.answerstr = false
      } else {
        this.answerstr = true
      }
      // console.log(this.answerstr)
      localStorage.setItem('paperId', row.id)
      getPaperResults(getResults).then((result) => {
        this.studentdetails = result.data
        console.log(this.studentdetails)
        this.examdetailsShow = true
        // console.log(this.studentdetails[0].paperResult.subjective_score === 0)
        // console.log(this.studentdetails[0].paperResult.length)
        // for (let i = 0; i < this.studentdetails.length; i++) {
        //   if (this.studentdetails[i].paperResult.length) {
        //     this.studentdetails[i].answersBolen = true
        //   } else {
        //     if (this.studentdetails[i].paperResult.subjective_score === 0) {
        //       this.studentdetails[i].answersBolen = true
        //     }
        //   }
        // }
      })
    },
    publicF(row) {
      if (row.status === 1) {
        return '取消发布'
      } else {
        return '发布'
      }
    },
    changeStatus(row) {
      // console.log(row)
      const changeStatus = {}
      changeStatus.id = row.id
      if (row.status === 0) {
        changeStatus.status = 1
      } else {
        changeStatus.status = 0
      }
      changePaperStatus(changeStatus).then((result) => {
        this.$message({
          message: '恭喜你，操作成功!',
          type: 'success'
        })
        this.getList()
        // console.log(result)
      })
    },
    resultStatusF(row) {
      if (row.resultStatus === 0) {
        this.startExamShow = true
        return '试卷未完成'
      } else if (row.resultStatus === 1) {
        this.startExamShow = false
        return '正在考试'
      } else {
        this.startExamShow = false
        return '已交卷'
      }
    },
    TimeF() {
      const _this = this
      this.timeid1 = setInterval(function() {
        _this.surplerTime = _this.surplerTime - 1
        if (_this.surplerTime < 0) {
          // 自动提交/*/
          // console.log('时间到了')
          clearInterval(_this.timeid1)
          _this.submitPaperF()
        }
      }, 1000)
    },
    editTimeF(row) {
      if (row.edit_time === 0) {
        return '暂无编辑'
      } else {
        return parseTime(row.edit_time)
      }
    },
    editNameF(row) {
      if (row.editName === null) {
        return '无'
      } else {
        return row.editName
      }
    },
    //  撒反对
    examDialog() {
      if (this.$refs.tree) {
        this.$refs.tree.setCheckedKeys([])
      }
      this.examShow = true
      this.resetForm()
    },
    getList() {
      this.pageList.classId = parseInt(localStorage.getItem('classId'))
      // console.log(this.pageList)
      examList(this.pageList).then((result) => {
        this.examAllList = result.data.papers
        this.totalcount = result.data.totalCount
        // console.log(result.data)
      }).catch(() => {})
    },
    send() {
      // console.log(this.form)
      if (this.form.rule.answer.count > 0) {
        this.form.isNeedRead = 1
      } else {
        this.form.isNeedRead = 0
      }
      this.form.classId = parseInt(localStorage.getItem('classId'))
      // 计算单题总分
      // console.log(this.$refs.tree.getCheckedKeys())
      this.checkBoxId = this.$refs.tree.getCheckedKeys()
      for (let i = 0; i < this.checkBoxId.length; i++) {
        // console.log(this.checkBoxId[i].substring(0, 2))
        if (this.checkBoxId[i].substring(0, 2) === 'cu') {
          this.form.rule.courseIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else if (this.checkBoxId[i].substring(0, 2) === 'cs') {
          this.form.rule.courseStageIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else {
          this.form.rule.knowledgeIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        }
      }
      this.checkBoxId = this.$refs.tree.getHalfCheckedKeys()
      for (let i = 0; i < this.checkBoxId.length; i++) {
        // console.log(this.checkBoxId[i].substring(0, 2))
        if (this.checkBoxId[i].substring(0, 2) === 'cu') {
          this.form.rule.courseIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else if (this.checkBoxId[i].substring(0, 2) === 'cs') {
          this.form.rule.courseStageIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else {
          this.form.rule.knowledgeIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        }
      }
      // console.log(this.form.rule.knowledgeIds)
      // console.log(this.form.rule.courseStageIds)
      // console.log(this.form.rule.courseIds)
      this.form.time = parseInt(this.second * 60)
      // console.log(this.form)
      if (parseInt(this.form.rule.totalscore) === 100) {
        addExam(this.form).then((result) => {
          // console.log(result.data)
          this.examShow = false
          this.$message({
            showClose: true,
            message: '添加试卷成功!!!',
            type: 'success'
          })
          this.getList()
        }).catch(() => {})
      } else {
        this.$message({
          showClose: true,
          message: '分数不正确!!!',
          type: 'error'
        })
      }
    },
    handleChange() {
      this.form.rule.choice.count = (this.form.rule.choice.hard + this.form.rule.choice.middle + this.form.rule.choice.easy)
      this.form.rule.judge.count = (this.form.rule.judge.hard + this.form.rule.judge.middle + this.form.rule.judge.easy)
      this.form.rule.answer.count = (this.form.rule.answer.hard + this.form.rule.answer.middle + this.form.rule.answer.easy)
      this.form.rule.totalscore = this.form.rule.choice.count * this.form.rule.choice.score + this.form.rule.judge.count * this.form.rule.judge.score + this.form.rule.answer.count * this.form.rule.answer.score
      this.form.rule.totalCount = (this.form.rule.answer.hard + this.form.rule.answer.middle + this.form.rule.answer.easy) + (this.form.rule.judge.hard + this.form.rule.judge.middle + this.form.rule.judge.easy) + (this.form.rule.choice.hard + this.form.rule.choice.middle + this.form.rule.choice.easy)
      if (this.form.rule.totalscore > 100) {
        this.$message({
          message: '请修改! 总分大于100分!',
          type: 'warning'
        })
      } else if (this.form.rule.totalscore === 100) {
        this.$message({
          message: '100分了!不能再多了',
          type: 'success'
        })
      }
    },
    resetForm() {
      this.form.title = undefined
      this.form.classid = undefined
      this.form.time = null
      this.form.isNeedRead = 0
      this.form.rule.courseIds = []
      this.form.rule.courseStageIds = []
      this.form.rule.knowledgeIds = []
      this.form.rule.totalCount = null
      this.form.rule.totalscore = null
      this.form.rule.choice.count = null
      this.form.rule.choice.hard = null
      this.form.rule.choice.middle = null
      this.form.rule.choice.easy = null
      this.form.rule.choice.score = null
      this.form.rule.judge.count = null
      this.form.rule.judge.hard = null
      this.form.rule.judge.middle = null
      this.form.rule.judge.easy = null
      this.form.rule.judge.score = null
      this.form.rule.answer.count = null
      this.form.rule.answer.hard = null
      this.form.rule.answer.middle = null
      this.form.rule.answer.easy = null
      this.form.rule.answer.score = null
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  .question-number:hover {
    background-color: #409EFF !important;
    color: #fff !important;
  }
.dashboard {
  &-container {
    margin: 10px;
  }
  &-text {
    font-size: 30px;
    line-height: 46px;
  }
}
.el-row {
  margin-bottom: 20px;
  &:last-child {
    margin-bottom: 0;
  }
}
.el-col {
  border-radius: 4px;
}
.bg-purple-dark {
  background: #99a9bf;
}
.bg-purple {
  background: #d3dce6;
}
.bg-purple-light {
  background: #e5e9f2;
}
.grid-content {
  border-radius: 4px;
  min-height: 36px;
  text-align: center;
  line-height: 36px;
  /*font-weight: bold;*/
  color: #303133;
}
.row-bg {
  padding: 10px 0;
  background-color: #f9fafc;
}
  .maxBox{
    width: 100%;
    height: 100%;
    /*background: skyblue;*/
    /*height: 1000px;*/
  }
.el-radio__label{
  font-size: 16px;
}
</style>
