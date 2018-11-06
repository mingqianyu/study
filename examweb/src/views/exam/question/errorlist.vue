<template>
  <div>
    <el-button type="primary" plain @click="seachError">筛选</el-button>
    <el-dialog
      :visible.sync="errorSeachShow"
      title="考试详情"
      width="40%"
      style="min-width: 1000px;">
      <p>请选择错题集范围:</p>
      <el-tree
        ref="tree"
        :data="zuidashu"
        :props="defaultProps"
        :default-expanded-keys="['cu9']"
        :default-checked-keys="['su1']"
        show-checkbox
        node-key="value">
        <!--kl-->
      </el-tree>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="getList">确 定</el-button>
      </span>
    </el-dialog>
    <el-table
      :data="questionValList"
      border
      style="width: 100%;margin-left: 0%;margin-top: 10px;">
      <el-table-column
        prop="title"
        label="题目名称"
        align="center"
        width="150">
        <template slot-scope="scope">
          <span>{{ scope.row.title | unescape }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="title"
        label="题目答案"
        align="center"
        width="100">
        <template slot-scope="scope">
          <span>{{ scope.row.answer | unescape }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="title"
        label="题目选项"
        align="center">
        <template slot-scope="scope">
          <span>{{ optionsF(scope.row) | unescape }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="title"
        label="题目解析"
        align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.analysis | unescape }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="title"
        label="错误次数"
        align="center"
        width="50">
        <template slot-scope="scope">
          <span>{{ scope.row.count }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="title"
        label="知识点"
        align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.knowledgeName | unescape }}</span>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import { getErrorQuestions } from '../../../api/exam'
import questionView from './index'
import judgeView from './index'
import answer from './index'

export default {
  name: 'Errorlist',
  components: { questionView, judgeView, answer },
  props: {
    zuidashu: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      studentChoiceList: [],
      studentJudgeList: [],
      studentAnswerList1: [],
      defaultProps: {
        children: 'children',
        label: 'label'
      },
      answerstr: 'A',
      ming: '',
      errorcList: {
        category: {
          courseIds: [],
          courseStageIds: [],
          knowledgeIds: []
        },
        page: 1,
        pageSize: 5
      },
      errorSeachShow: false,
      questionValList: []
    }
  },
  created() {
    // this.getList()
  },
  methods: {
    optionsF(row) {
      return row.options.split('[++++]').join(',')
    },
    seachError() {
      this.errorSeachShow = true
    },
    getList() {
      this.errorSeachShow = false
      // console.log(this.$refs.tree.getCheckedKeys())
      this.checkBoxId = this.$refs.tree.getCheckedKeys()
      for (let i = 0; i < this.checkBoxId.length; i++) {
        if (this.checkBoxId[i].substring(0, 2) === 'cu') {
          this.errorcList.category.courseIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else if (this.checkBoxId[i].substring(0, 2) === 'cs') {
          this.errorcList.category.courseStageIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else if (this.checkBoxId[i].substring(0, 2) === 'kn') {
          this.errorcList.category.knowledgeIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        }
      }
      this.checkBoxId = this.$refs.tree.getHalfCheckedKeys()
      for (let i = 0; i < this.checkBoxId.length; i++) {
        if (this.checkBoxId[i].substring(0, 2) === 'cu') {
          this.errorcList.category.courseIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else if (this.checkBoxId[i].substring(0, 2) === 'cs') {
          this.errorcList.category.courseStageIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        } else if (this.checkBoxId[i].substring(0, 2) === 'kn') {
          this.errorcList.category.knowledgeIds.push(parseInt(this.checkBoxId[i].substring(2, this.checkBoxId[i].length)))
        }
      }
      // console.log(this.errorcList)
      getErrorQuestions(this.errorcList).then((result) => {
        // console.log(result.data)
        this.studentChoiceList = []
        this.questionValList = result.data.errorQuestions
        for (let i = 0; i < this.questionValList.length; i++) {
          if (this.questionValList[i].type === 'choice') {
            this.studentChoiceList.push(this.questionValList[i])
          } else if (this.questionValList[i].type === 'judge') {
            this.studentJudgeList.push(this.questionValList[i])
          } else if (this.questionValList[i].type === 'answer') {
            this.studentAnswerList1.push(this.questionValList[i])
          }
        }
      })
    }
  }
}
</script>

<style scoped>

</style>
