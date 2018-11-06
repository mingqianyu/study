<template>
  <div class="app-container">
    <div style="margin-bottom: 20px;">
      <el-select v-model="pageListSubjectid" clearable placeholder="请选择学科" @change="seachWay">
        <el-option
          v-for="item in subjectList"
          :key="item.name"
          :label="item.name"
          :value="item.id">
          <span>{{ item.name }}</span>
        </el-option>
      </el-select>
      <el-input v-model="subjectListName" :onchange="seachWay" class="title-input" style="width: 200px;height: 40px;" placeholder="请输入课程阶段名称" clearable @change="seachWay">
        {{ ming }}
      </el-input>
      <el-button class="filter-item" type="primary" icon="el-icon-search" style="margin-left: 0.5%;" @click="seachWay" >搜索</el-button>
      <el-button class="filter-item" style="margin-left: 0.5%;" icon="el-icon-edit" type="primary" @click="append">添加</el-button>
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
        prop="name"
        label="阶段名称"
        align="center"
        width="150">{{ ming }}
      </el-table-column>
      <el-table-column
        prop="subject_id"
        label="所属学科"
        align="center"
        width="150">
        <template slot-scope="scope">
          <span>{{ subjectname(scope.row.subject_id) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="knowledge_names"
        label="所包含的知识点"
        min-width="300">
        <template slot-scope="scope">
          <span>{{ scope.row.knowledge_names | unescape }}</span>
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
        label="阶段状态"
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
        <el-form-item label="课程阶段名称" prop="name" style="width: 400px">
          <el-input v-model="form.name">{{ ming }}</el-input>
        </el-form-item>
        <div style="margin-bottom: 22px;">
          <span style="font-size: 14px;text-align: right;color: #606266;line-height: 40px;padding: 0 12px 0 0; box-sizing: border-box;width: 100px;font-weight: bold;">阶段所属学科</span>
          <el-select v-model="form.subjectId" clearable placeholder="请选择学科" @change="changeSubject">
            <el-option
              v-for="item in valsubjectList"
              :key="item.name"
              :label="item.name"
              :value="item.id">
              <span>{{ item.name }}</span>
            </el-option>
          </el-select>
        </div>
        <div style="margin-bottom: 22px;">
          <span style="font-size: 14px;text-align: right;color: #606266;line-height: 40px;padding: 0 12px 0 13px; box-sizing: border-box;width: 100px;font-weight: bold;">知识点选择</span>
          <el-select v-model="knowId" clearable filterable placeholder="请选择知识点!" @change="changeValue" >
            <el-option
              v-for="item in selectKnowledge"
              :key="item.name"
              :label="item.name"
              :value="item.id">
              <span>{{ item.name | unescape }}</span>
            </el-option>
          </el-select>
        </div>
        <!--选中知识点展示列表-->
        <div style="margin-bottom: 22px;display: flex;">
          <p style="font-size: 14px;width: 83px;padding: 0; text-align: right;color: #606266;line-height: 40px; box-sizing: border-box; font-weight: bold;">知识点列表</p>
          <!--<el-input :disabled="true" v-model="qian" type="textarea" placeholder="课程阶段列表" >{{ ming }}</el-input>-->
          <div style="width: 400px;border: 1px solid #ccc;border-radius: 5px;padding:5px 8px 5px 5px;margin-left: 16px">
            <el-tag
              v-for="tag in tags"
              :key="tag.name"
              type="success"
              closable
              style="margin-top: 5px;margin-left: 5px;"
              size="mini"
              @close="handleClose(tag)">
              {{ tag.name | unescape }}
            </el-tag>
          </div>
        </div>
        <el-form-item label="课程阶段状态" prop="status">
          <el-select v-model="form.status" placeholder="请选择课程阶段状态">
            <el-option v-for="item in statusOption" :key="item.value" :label="item.name" :value="item.value">{{ item.name | unescape }}</el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button v-if="dialogStatus==='create'" type="primary" @click="send()">确 定</el-button>
        <el-button v-else type="primary" @click="addition">确 定</el-button>
        <el-button @click="cancelid">取 消</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { knowList } from '../../api/know'
import { subjectList, valsubjectList } from '../../api/form'
import { courseList, addCourse, deletesto, editCourse } from '../../api/tree'
import { unescape, parseTime } from '../../filters'

export default {

  data() {
    return {
      tags: [],
      knowAllList: [],
      knowListTxt: '',
      knowidList: [],
      knowId: null,
      filterText: '',
      data2: [{
        id: 1,
        label: 'Level one 1'
      }],
      ming: '',
      qian: '',
      subjectList: [],
      valsubjectList: [],
      pageListSubjectid: null,
      pageList: {
        subjectId: null,
        name: undefined,
        page: 1,
        pageSize: 5
      },
      selectKnowledge: [],
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
      subjectListName: '',
      visitKnow: false,
      textMap: {
        updata: '编辑',
        create: '添加'
      },
      dialogStatus: '',
      form: {
        name: '',
        status: 1,
        knowledgeIds: [],
        subjectId: null,
        id: null
      },
      checkedCount: null,
      checkedCities: [],
      tanchaugnID: null,
      knowlistAll: [],
      delId: {
        id: null
      },
      totalcount: null,
      errortxt: '',
      courseSubjectid: null,
      num: 1,
      knowsubject: {
        subjectId: null
      }
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
      this.subjectList = result.data
    })
    // 获取所有有效学科
    valsubjectList().then((result) => {
      this.valsubjectList = result.data
    })
    // 获取知识点列表
    knowList().then((result) => {
      this.knowAllList = result.data.knowledgeList
    })
    this.getList()
  },
  methods: {
    editNameF(row) {
      if (row.editName === '') {
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
    changeSubject() {
      // console.log('学科一改变')
      // 清空下面知识点选择和知识点列表
      this.tags = []
      // 获取知识点列表
      if (this.form.subjectId !== '') {
        this.knowsubject.subjectId = this.form.subjectId
      } else {
        this.knowsubject.subjectId = null
      }
      knowList(this.knowsubject).then((result) => {
        this.knowAllList = result.data.knowledgeList
        this.knowId = null
      }).catch(() => {})
    },
    subjectname(id) {
      for (let c = 0; c < this.subjectList.length; c++) {
        if (this.subjectList[c].id === parseInt(id)) {
          return this.subjectList[c].name
        }
      }
    },
    handleClose(tag) {
      this.selectKnowledge.push(tag)
      this.tags.splice(this.tags.indexOf(tag), 1)
    },
    changeValue() {
      //  添加标签
      if (this.knowId) {
        for (let i = 0; i < this.selectKnowledge.length; i++) {
          if (parseInt(this.selectKnowledge[i].id) === parseInt(this.knowId)) {
            this.tags.push(this.selectKnowledge[i])
            this.selectKnowledge.splice(this.selectKnowledge.indexOf(this.selectKnowledge[i]), 1)
            this.knowId = null
          }
        }
      }
      // console.log(this.tags)
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
      //  编辑课程阶段
      this.resetFrom()
      // console.log(row)
      this.form.id = row.id
      this.dialogStatus = 'updata'
      this.form.name = unescape(row.name)
      this.form.subjectId = row.subject_id
      this.form.knowledgeIds = row.knowledge_ids
      this.form.status = row.status
      for (let j = 0; j < this.knowAllList.length; j++) {
        let isHave = false
        for (var i = 0; i < this.form.knowledgeIds.length; i++) {
          if (parseInt(this.form.knowledgeIds[i]) === parseInt(this.knowAllList[j].id)) {
            isHave = true
            this.tags.push(this.knowAllList[j])
            break
          }
        }
        if (!isHave) {
          this.selectKnowledge.push(this.knowAllList[j])
        }
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
        deletesto(this.delId).then(() => {
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
      // console.log(this.pageListSubjectid === '')
      if (this.pageListSubjectid === '') {
        this.pageList.subjectId = null
        this.pageList.subjectId = 0
        // console.log(this.pageList.subjectId)
      } else {
        this.pageList.subjectId = this.pageListSubjectid
        // console.log(this.pageList.subjectId)
      }
      if (this.subjectListName) {
        this.pageList.name = this.subjectListName
      } else {
        this.pageList.name = undefined
      }
      this.getList()
    },
    addition() {
      // 修改提交按钮
      this.form.knowledgeIds = []
      for (let a = 0; a < this.tags.length; a++) {
        this.form.knowledgeIds.push(parseInt(this.tags[a].id))
      }
      // console.log(this.form)
      editCourse(this.form).then(() => {
        this.visitKnow = false
        this.getList()
      }).catch(() => {
        this.$notify({ title: '失败', message: '修改知识点失败！', type: 'error', duration: 2000 })
      })
    },
    send() {
      // 添加知识点
      this.form.knowledgeIds = []
      for (let a = 0; a < this.tags.length; a++) {
        this.form.knowledgeIds.push(parseInt(this.tags[a].id))
      }
      // console.log(this.form)
      // console.log(123)
      //   添加课程阶段
      addCourse(this.form).then(() => {
        // console.log('添加成功')
        this.visitKnow = false
        this.getList()
      }).catch(() => {})
    },
    handleCheckedCitiesChange(value) {
      this.checkedCount = value.length
      // this.checkAll = checkedCount === this.cities.length;
      // this.isIndeterminate = checkedCount > 0 && checkedCount < this.cities.length;
      this.form.subjectIds = value
      // console.log(this.tanchaugnID)
    },
    filterNode(value, data) {
      if (!value) return true
      return data.label.indexOf(value) !== -1
    },
    cancelid() {
      this.getList()
      this.visitKnow = false
    },
    getList() {
      // 获取阶段列表
      courseList(this.pageList).then((result) => {
        this.totalcount = result.data.totalCount
        this.knowlistAll = result.data.courseStageList
      }).catch((result) => {
        this.errortxt = result.response.data.msg
        this.$notify({ title: '失败', message: this.errortxt, type: 'error', duration: 2000 })
      })
      // 获取知识点列表
      knowList().then((result) => {
        this.knowAllList = result.data.knowledgeList
      }).catch(() => {})
    },
    append() {
      this.resetFrom()
      for (let i = 0; i < this.knowAllList.length; i++) {
        this.selectKnowledge.push(this.knowAllList[i])
      }
      this.visitKnow = true
      this.dialogStatus = 'create'
      this.tags = []
    },
    resetFrom() {
      this.selectKnowledge = []
      this.form.name = ''
      this.form.knowledgeIds = []
      this.form.subjectId = null
      this.form.status = 1
      this.qian = ''
      this.form.id = null
      this.knowidList = []
      this.tags = []
      this.knowId = null
    }
  }
}
</script>
<style>
  .el-textarea.is-disabled .el-textarea__inner {
    height: 200px;
  }
</style>
