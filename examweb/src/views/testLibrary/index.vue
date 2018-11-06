<template>
  <div class="app-container">
    <div style="margin-bottom: 20px;display: flex;">
      <el-input v-model="pageList.title" class="title-input" style="width: 150px; height: 40px;" placeholder="请输入题目名称" clearable @change="seachWay">
        {{ ming }}
      </el-input>
      <el-select v-model="pageList.subjectId" style="width: 150px;margin-left: .5%;" clearable placeholder="请选择学科" @change="seachWay">
        <el-option
          v-for="item in subjectList"
          :key="item.name"
          :label="item.name"
          :value="item.id">
          <span>{{ item.name }}</span>
        </el-option>
      </el-select>
      <el-select v-model="pageList.level" style="width: 170px;margin-left: .5%;" clearable placeholder=" 请选择题目难度" @change="seachWay">
        <el-option
          v-for="item in statusOption1"
          :key="item.name"
          :label="item.name"
          :value="item.value">
          <span>{{ item.name }}</span>
        </el-option>
      </el-select>
      <el-select v-model="pageList.type" style="width: 170px;margin-left: .5%;" clearable placeholder="请选择题目类型" @change="seachWay">
        <el-option
          v-for="item in statusOption21"
          :key="item.name"
          :label="item.name"
          :value="item.value">
          <span>{{ item.name }}</span>
        </el-option>
      </el-select>
      <el-button class="filter-item" type="primary" icon="el-icon-search" style="margin-left: .5%;" @click="seachWay" >搜索</el-button>
      <el-button class="filter-item" style="margin-left: 0.5%;" icon="el-icon-edit" type="primary" @click="append">添加</el-button>
      <el-button class="filter-item" style="margin-left: 0.5%;" icon="el-icon-message" type="primary" @click="upupf">导入</el-button>
    </div>
    <el-table
      :data="knowlistAll"
      border
      style="width: 100%">
      <el-table-column
        type="index"
        align="center"
        width="50">
        <!--/-->
      </el-table-column>
      <el-table-column
        prop="title"
        label="题目名称"
        align="center"
        min-width="250">
        <template slot-scope="scope">
          <span>{{ scope.row.title | unescape }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="type"
        label="题目类型"
        align="center"
        width="100">
        <template slot-scope="scope">
          <span>{{ typef(scope.row) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="options"
        label="题目选项"
        min-width="240">
        <template slot-scope="scope">
          <span>{{ xuanF(scope.row) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="answer"
        label="题目答案"
        width="300">
        <template slot-scope="scope">
          <span>{{ answerF(scope.row) | unescape }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="analysis"
        label="题目解析"
        min-width="300">
        <template slot-scope="scope">
          <span>{{ scope.row.analysis | unescape }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="knowledgeName"
        label="知识点"
        align="center"
        width="200">
        <template slot-scope="scope">
          <span>{{ scope.row.knowledgeName }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="level"
        label="题目难度"
        align="center"
        width="100">
        <template slot-scope="scope">
          <span>{{ levelF(scope.row) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="isInterview"
        label="面试题"
        align="center"
        width="110">
        <template slot-scope="scope">
          <span>{{ scope.row.isInterview === 1 ? '面试题' : '否' }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="createName"
        label="创建人"
        align="center"
        width="100">
        <template slot-scope="scope">
          <span>{{ scope.row.createName }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="create_time"
        label="创建时间"
        align="center"
        width="180">
        <template slot-scope="scope">
          <span>{{ scope.row.create_time | parseTime }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="editName"
        label="编辑人"
        align="center"
        width="100">
        <template slot-scope="scope">
          <span>{{ editNameF(scope.row) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="edit_time"
        label="编辑时间"
        align="center"
        width="180">
        <template slot-scope="scope">
          <span>{{ editTimeF(scope.row) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="status"
        label="题目状态"
        align="center"
        width="150">
        <template slot-scope="scope">
          <span>{{ scope.row.status === 0 ? '无效' : '有效' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center" width="200" fixed="right">
        <template slot-scope="scope" >
          <el-button type="primary" size="small" plain @click="edit(scope.row)">编辑</el-button>
          <el-button type="warning" size="small" plain @click="deleteKnow(scope.row.id)">删除</el-button>
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
    <el-dialog :title="textMap[dialogStatus]" :visible.sync="visitKnow" style="width: 90%;min-width: 1100px;margin: 0 auto">
      <el-form :model="form" label-width="100px" style="width: 600px">
        <el-form-item label="题目标题" prop="title" style="width: 400px">
          <el-input v-model="form.title">{{ ming }}</el-input>
        </el-form-item>
        <el-form-item label="题目类型" prop="status">
          <el-select v-model="form.type" placeholder="请选择题目类型" @change="showchoice">
            <el-option v-for="item in statusOption21" :key="item.value" :label="item.name" :value="item.value">{{ item.name }}</el-option>
          </el-select>
        </el-form-item>
        <!--判断题div-->
        <div v-if="checkpanduan">
          <el-form-item label="题目答案" prop="status">
            <el-select v-model="form.answer" placeholder="请选择题目答案" style="width: 300px">
              <el-option v-for="item in statusOption11" :key="item.value" :label="item.name" :value="item.value">{{ item.name }}</el-option>
            </el-select>
          </el-form-item>
        </div>
        <!--简答div-->
        <div v-if="checkjianda">
          <el-form-item label="题目答案" prop="status">
            <el-input v-model="form.answer" style="width: 400px;" type="textarea" autosize >{{ ming }}</el-input>
          </el-form-item>
        </div>
        <!--选择题div-->
        <div v-if="checktimu">
          <el-form-item label="题目选项" prop="status">
            <div v-for="option in options" id="buttongaobb" :key="option" style="display: flex;align-items: center;" class="buttongao">
              <span style="font-size: 16px;text-align: center;padding-top: 10px;margin-right: 10px;">{{ option }} : </span>
              <el-input
                v-model="optionValue[option]"
                placeholder="请输入选项"
                style="width: 300px;margin-top: 10px;"
                class="mingdiv"
                clearable>
                {{ ming }}
              </el-input>
              <i v-if="option !== 'A'" :id="option" style="font-size: 30px;color: #409EFF;padding-top: 10px;margin-left: 10px" class="el-icon-remove" @click="delOption(option)">
                <!---->
              </i>
              <i v-else class="el-icon-circle-plus" style="margin-left: 1.5%;font-size: 30px;color: #409EFF;margin-left: 10px;" @click="addxuan">
                <!---->
              </i>
            </div>
            <div id="mingDiv">
              <!---->
            </div>
          </el-form-item>
          <el-form-item label="题目答案" prop="status">
            <el-select v-model="form.answer" placeholder="请选择题目答案" style="width: 300px">
              <el-option v-for="item in options" :key="item.index" :label="item.value" :value="item">{{ item }}</el-option>
            </el-select>
          </el-form-item>
        </div>
        <el-form-item label="题目解析" prop="name" style="width: 400px">
          <el-input v-model="form.analysis" type="textarea" autosize >{{ ming }}</el-input>
        </el-form-item>
        <el-form-item label="题目难度" prop="status">
          <el-select v-model="form.level" placeholder="请选择题目难易度">
            <el-option v-for="item in statusOption1" :key="item.value" :label="item.name" :value="item.value">{{ item.name }}</el-option>
          </el-select>
        </el-form-item>
        <!--筛选题目了-->
        <el-form-item label="所属知识点">
          <el-cascader
            v-model="mingqianyu"
            :options="zuidashu"
            size="medium"
            style="width: 300px"
            expand-trigger="hover"
            clearable
            @change="handleChange">
            {{ ming }}
          </el-cascader>
          <el-select v-model="form.knowledgeId" clearable filterable placeholder="请选择知识点!" style="margin-top: 1%;" @change="changeValue" >
            <el-option
              v-for="item in knowAllList"
              :key="item.name"
              :label="item.name"
              :value="item.id">
              <span>{{ item.name | unescape }}</span>
            </el-option>
          </el-select>
        </el-form-item>
        <!--筛选课程阶段-->
        <el-form-item label="题目状态" prop="status">
          <el-select v-model="form.status" placeholder="请选择题目状态">
            <el-option v-for="item in statusOption" :key="item.value" :label="item.name" :value="item.value">{{ item.name }}</el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="是否为面试题" prop="status">
          <el-select v-model="form.isInterview" placeholder="请选择题目状态">
            <el-option v-for="item in statusOption33" :key="item.value" :label="item.name" :value="item.value">{{ item.name }}</el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button v-if="dialogStatus==='create'" type="primary" @click="send()">确 定</el-button>
        <el-button v-else type="primary" @click="addition">确 定</el-button>
        <el-button @click="visitKnow = false">取 消</el-button>
      </div>
    </el-dialog>
    <el-dialog
      :visible.sync="uploadShow"
      title="题目导入"
      width="30%"
      style="min-width: 800px;">
      <div style="display: flex;flex-direction: column;align-items: center">
        <el-upload
          :on-preview="handlePreview"
          :on-remove="handleRemove"
          :before-remove="beforeRemove"
          :limit="10"
          :on-exceed="handleExceed"
          :on-error="errorF"
          :on-success="successF"
          :headers = "{ 'X-Token': token, 'X-Requested-With': 'XMLHttpRequest' }"
          name="question"
          class="upload-demo"
          multiple
          drag
          action="/importQuestion">
          <i class="el-icon-upload">
            <!---->
          </i>
          <div class="el-upload__text">将文件拖到此处，或<em>点击导入</em></div>
          <div slot="tip" class="el-upload__tip" style="color: #F56C6C;">* 只能上传Excel文件</div>
        </el-upload>
      </div>
      <el-dialog
        :visible.sync="innerVisible"
        width="50%"
        title="上传信息"
        append-to-body>
        <div :style="{ color: showcolor }" style="text-align: center;font-size: 16px;font-weight: bold;margin-top: -20px;"><p>{{ showtxt }}</p></div>
        <div :style="{ color: showcolor }">{{ updatamessage | unescape }}</div>
      </el-dialog>
    </el-dialog>
  </div>
</template>

<script>
import { subjectList, valsubjectList } from '../../api/form'
import { unescape, parseTime } from '../../filters'
import { getAllCategoryTree, addquestion, questionAllList, editQuest, deleteques, importQuestion } from '../../api/testLibrary'
import { getToken } from '@/utils/auth'
import { knowList } from '../../api/know'

export default {

  data() {
    return {
      zuidashu: [],
      updatamessage: '',
      innerVisible: false,
      uploadShow: false,
      knowAllList: [],
      mingqianyu: null,
      checktimu: true,
      kecheng: null,
      checkpanduan: false,
      checkjianda: false,
      xuanone: '',
      filterText: '',
      questionName: '',
      showcolor: '',
      questionitem1: [],
      data2: [{
        id: 1,
        label: 'Level one 1'
      }],
      ming: '',
      token: '',
      subjectList: [],
      valssubjectList: [],
      pageListSubjectid: null,
      pageList: {
        subjectId: null,
        title: undefined,
        type: undefined,
        level: undefined,
        page: 1,
        pageSize: 5
      },
      problemList: {
        subjectId: null
      },
      options: [
        'A'
      ],
      optionValue: {
        A: ''
      },
      statusOption: [
        {
          name: '有效',
          value: 1
        },
        {
          name: '无效',
          value: 0
        }
      ],
      statusOption33: [
        {
          name: '面试题',
          value: 1
        },
        {
          name: '不是面试题',
          value: 0
        }
      ],
      statusOption11: [
        {
          name: '正确',
          value: 'T'
        },
        {
          name: '错误',
          value: 'F'
        }
      ],
      statusOption1: [
        {
          name: '简单',
          value: 'easy'
        },
        {
          name: '中等',
          value: 'middle'
        },
        {
          name: '困难',
          value: 'hard'
        }
      ],
      statusOption2: [
        {
          name: 'A',
          value: 'A'
        },
        {
          name: 'B',
          value: 'B'
        },
        {
          name: 'C',
          value: 'C'
        },
        {
          name: 'D',
          value: 'D'
        }
      ],
      statusOption21: [
        {
          name: '选择题',
          value: 'choice'
        },
        {
          name: '判断题',
          value: 'judge'
        },
        {
          name: '简答题',
          value: 'answer'
        }
      ],
      subjectListName: '',
      visitKnow: false,
      textMap: {
        updata: '编辑',
        create: '添加'
      },
      dialogStatus: '',
      form: {
        title: '',
        status: 1,
        id: null,
        options: [],
        answer: '',
        analysis: '',
        knowledgeId: null,
        type: 'judge',
        level: 'easy',
        category: '',
        isInterview: 0
      },
      checkedCount: null,
      checkedCities: [],
      tanchaugnID: null,
      knowlistAll: [
        {
          id: 1,
          title: '第一题',
          options: '选线',
          answer: '问题答案',
          analysis: '答案解析',
          knowledge_id: 12,
          type: '选择题',
          level: '简单题',
          create_user_id: 1,
          create_time: 1238484848,
          edit_user_id: 1,
          edit_time: 1353234352,
          status: 1
        }
      ],
      delId: {
        id: null
      },
      totalcount: null,
      errortxt: '',
      questionitem2: [],
      showtxt: ''
    }
  },
  watch: {
    filterText(val) {
      this.$refs.tree2.filter(val)
    }
  },
  created() {
    // 获取所有学科
    subjectList().then((result) => {
      // console.log(result.data)
      this.subjectList = result.data
    })
    // 获取知识点列表
    knowList().then((result) => {
      this.knowAllList = result.data.knowledgeList
    })
    // 获取所有有效学科
    valsubjectList().then((result) => {
      // console.log(result.data)
      this.valssubjectList = result.data
      this.questionitem1 = result.data
    })
    // 获取最大树
    getAllCategoryTree().then((result) => {
      this.zuidashu = result.data
      this.token = getToken()
      // console.log(this.token)
    })
    this.getList()
  },
  methods: {
    changeValue() {
      this.mingqianyu = []
    },
    upupf() {
      this.uploadShow = true
    },
    upsendF() {
      this.getList()
      this.uploadShow = false
    },
    handlePreview(file) {
      // console.log(file)
    },
    errorF(error, file, fileList) {
      // console.log(JSON.parse(error.message).msg)
      this.innerVisible = true
      this.showcolor = '#F56C6C'
      this.showtxt = '导入失败'
      this.updatamessage = JSON.parse(error.message).msg
    },
    successF(response, file, fileList) {
      this.innerVisible = true
      this.updatamessage = response.msg
      // console.log(response)
      this.showcolor = '#67C23A'
      this.showtxt = '导入成功'
      this.getList()
    },
    handleRemove(file, fileList) {
      // console.log(file, fileList)
    },
    handleExceed(files, fileList) {
      this.$message.warning(`当前限制选择 1 个文件，本次选择了 ${files.length} 个文件，共选择了 ${files.length + fileList.length} 个文件`)
    },
    beforeRemove(file, fileList) {
      return this.$confirm(`确定移除${file.name}吗?`)
    },
    importF() {
      importQuestion().then(() => {})
    },
    editNameF(row) {
      if (row.editName === null) {
        return '无'
      } else {
        return row.editName
      }
    },
    editTimeF(row) {
      if (row.edit_time === 0) {
        return '暂无编辑'
      } else {
        return parseTime(row.edit_time)
      }
    },
    levelF(row) {
      if (row.level === 'easy') {
        return '简单'
      } else if (row.level === 'middle') {
        return '中等'
      } else {
        return '困难'
      }
    },
    answerF(row) {
      if (row.answer === 'T') {
        return '正确'
      } else if (row.answer === 'F') {
        return '错误'
      } else {
        return row.answer
      }
    },
    typef(row) {
      if (row.type === 'judge') {
        return '判断题'
      } else if (row.type === 'choice') {
        return '选择题'
      } else if (row.type === 'answer') {
        return '简答题'
      }
    },
    xuanF(row) {
      return unescape(row.options.replace(/\[\+\+\+\+\]/g, ', '))
    },
    handleChange(value) {
      // console.log(value)
      // console.log(value.length !== 0)
      if (value.length !== 0) {
        this.form.knowledgeId = parseInt(value[value.length - 1])
        this.form.category = value.join(',')
      }
    },
    showchoice() {
      // console.log(this.form.type === 'choice')
      if (this.form.type === 'choice') {
        this.checktimu = true
        this.checkpanduan = false
        this.checkjianda = false
      } else {
        this.checktimu = false
      }
      if (this.form.type === 'judge') {
        this.checkpanduan = true
        this.checktimu = false
        this.checkjianda = false
      } else {
        this.checkpanduan = false
      }
      if (this.form.type === 'answer') {
        this.checkjianda = true
        this.checkpanduan = false
        this.checktimu = false
      } else {
        this.checkjianda = false
      }
    },
    addxuan() {
      const addOption = this.options.length + 1
      const char = String.fromCharCode(64 + parseInt(addOption))
      this.options.push(char)
    },
    delOption(option) {
      const newOptionValue = {}
      let index = 0
      for (let n = 0; n < this.options.length; n++) {
        if (this.options[n] !== option) {
          const newCharOption = String.fromCharCode(65 + parseInt(index))
          const charOption = String.fromCharCode(65 + parseInt(n))
          newOptionValue[newCharOption] = this.optionValue[charOption]
          index++
        }
      }
      this.optionValue = newOptionValue
      const opnum = this.options.length
      var newOptions = []
      for (let j = 0; j < opnum - 1; j++) {
        const char = String.fromCharCode(65 + parseInt(j))
        newOptions.push(char)
      }
      this.options = newOptions
    },
    handleSizeChange(val) {
      this.pageList.pageSize = val
      this.getList()
    },
    handleCurrentChange(val) {
      this.pageList.page = val
      this.getList()
    },
    edit(row) {
      //  编辑知识点
      // console.log(row)
      this.dialogStatus = 'edit'
      this.resetFrom()
      this.form.id = row.id
      this.form.analysis = unescape(row.analysis)
      this.form.answer = unescape(row.answer)
      this.form.title = unescape(row.title)
      this.form.status = row.status
      this.form.type = row.type
      this.form.level = row.level
      this.form.knowledgeId = row.knowledge_id
      this.form.isInterview = row.isInterview
      this.form.category = row.category
      this.form.options = row.options
      var num1 = row.options.split('[++++]')
      for (let u = 0; u < num1.length; u++) {
        this.options[u] = num1[u].substring(0, 1)
        this.optionValue[this.options[u]] = unescape(num1[u].substring(1, num1[u].length))
        // console.log(this.optionValue[this.options[u]])
        // console.log(num1[u].substring(1, num1.length))
      }
      this.mingqianyu = row.category.split(',')
      for (let k = 0; k < this.mingqianyu.length; k++) {
        this.mingqianyu[k] = parseInt(this.mingqianyu[k])
      }
      // console.log(this.mingqianyu)
      if (row.type === 'answer') {
        this.checkjianda = true
        this.checktimu = false
        this.checkpanduan = false
      } else if (row.type === 'judge') {
        this.checkjianda = false
        this.checktimu = false
        this.checkpanduan = true
      } else if (row.type === 'choice') {
        this.checkjianda = false
        this.checktimu = true
        this.checkpanduan = false
      }
      this.visitKnow = true
    },
    deleteKnow(id) {
      //  删除知识点
      this.delId.id = id
      this.$confirm('此操作将永久删除该文件, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        deleteques(this.delId).then(() => {
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
    seachsub(row) {
      var sub = ''
      var arr1 = row.subject_ids
      for (let i = 0; i < arr1.length; i++) {
        var num = parseInt(arr1[i])
        for (let j = 0; j < this.subjectList.length; j++) {
          if (num === parseInt(this.subjectList[j].id)) {
            sub += this.subjectList[j].name
            sub += ','
          }
        }
      }
      return sub
    },
    seachWay() {
      // console.log(this.pageList)
      if (this.pageList.level === '') {
        this.pageList.level = undefined
      }
      if (this.pageList.subjectId === '') {
        this.pageList.subjectId = null
      }
      if (this.pageList.type === '') {
        this.pageList.type = undefined
      }
      if (this.pageList.title === '') {
        this.pageList.title = undefined
      }
      this.getList()
    },
    addition() {
      // 修改提交按钮
      // console.log(this.form)
      editQuest(this.form).then(() => {
        this.visitKnow = false
        this.getList()
      }).catch(() => {
        this.$notify({ title: '失败', message: '修改知识点失败！', type: 'error', duration: 2000 })
      })
    },
    send() {
      // 添加知识点
      if (this.form.type === 'choice') {
        for (let a = 0; a < this.options.length; a++) {
          this.form.options[a] = this.options[a] + this.optionValue[this.options[a]]
        }
      }
      // console.log(this.form)
      addquestion(this.form).then(() => {
        // console.log('陈宫添加题目')
        this.visitKnow = false
        this.getList()
      }).catch(() => {
      })
    },
    handleCheckedCitiesChange(value) {
      this.checkedCount = value.length
      // console.log(value)
      this.form.subjectIds = value
    },
    filterNode(value, data) {
      if (!value) return true
      return data.label.indexOf(value) !== -1
    },
    getList() {
      questionAllList(this.pageList).then((result) => {
        // console.log(result)
        this.totalcount = result.data.totalCount
        this.knowlistAll = result.data.questions
      }).catch((result) => {
        this.errortxt = result.response.data.msg
        this.$notify({ title: '失败', message: this.errortxt, type: 'error', duration: 2000 })
      })
    },
    append() {
      this.resetFrom()
      this.visitKnow = true
      this.dialogStatus = 'create'
    },
    resetFrom() {
      this.form.title = undefined
      this.mingqianyu = []
      this.form.analysis = undefined
      this.form.options = []
      this.form.answer = undefined
      this.form.knowledgeId = null
      this.form.type = undefined
      this.form.level = undefined
      this.form.status = 1
      this.form.knowledgeId = null
      this.xuanone = undefined
      this.options = ['A']
      var inputnum = document.getElementById('mingDiv')
      var inum = document.getElementsByClassName('mingDiv')
      for (let m = 0; m < inum.length; m++) {
        inputnum.removeChild(inputnum[m])
      }
    }
  }
}
</script>
<style>
  .el-input--suffix .el-input__inner {
    /*width: 370px;*/
  }
  .buttongao{
    display: flex;
  }
  #mingDiv input {
    -webkit-appearance: none;
    background-color: #fff;
    background-image: none;
    border-radius: 4px;
    border: 1px solid #dcdfe6;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    color: #606266;
    display: inline-block;
    font-size: inherit;
    height: 40px;
    line-height: 40px;
    outline: 0;
    padding: 0 15px;
    -webkit-transition: border-color .2s cubic-bezier(.645,.045,.355,1);
    transition: border-color .2s cubic-bezier(.645,.045,.355,1);
    width: 100%;
    outline: none;
    /*border:none;*/
    width: 300px;
    margin-top: 10px;
  }
  #mingDiv input:empty::before{
    color: lightgrey;
  }
</style>
