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
      <!--<el-input v-model="subjectListName" :onchange="seachWay" class="title-input" style="width: 200px;height: 40px;" placeholder="请输入知识点名称" clearable>-->
      <!--{{ ming }}-->
      <!--</el-input>-->
      <el-button class="filter-item" type="primary" icon="el-icon-search" style="margin-left: 0.5%;" @click="seachWay" >搜索</el-button>
      <el-button class="filter-item" style="margin-left: 0.5%;" icon="el-icon-edit" type="primary" @click="append">添加</el-button>
    </div>
    <el-table
      :data="courseTableList"
      border
      style="width: 100%">
      <el-table-column
        prop="name"
        label="课程名称"
        align="center"
        width="200">
        <template slot-scope="scope">
          <span>{{ scope.row.name | unescape }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="subject_id"
        label="所属学科"
        align="center"
        width="100">
        <template slot-scope="scope">
          <span>{{ seachsub(scope.row) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="course_stage_names"
        label="课程阶段列表"
        min-width="300">
        <template slot-scope="scope">
          <span>{{ retrubSub(scope.row.course_stage_names) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="createName"
        label="创建人"
        align="center"
        width="100">{{ ming }}
      </el-table-column>
      <el-table-column
        prop="create_time"
        label="创建时间"
        align="center"
        width="160">
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
        width="160">
        <template slot-scope="scope">
          <span>{{ editTimeF(scope.row) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="status"
        label="课程状态"
        align="center"
        width="100">
        <template slot-scope="scope">
          <span>{{ scope.row.status === 0 ? '无效' : '有效' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center" width="240" fixed="right">
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
        <el-form-item label="课程名称" prop="name" style="width: 400px">
          <el-input v-model="form.name">{{ ming }}</el-input>
        </el-form-item>
        <div style="margin-bottom: 22px;">
          <span style="font-size: 14px;text-align: right;color: #606266;line-height: 40px;padding: 0 12px 0 0; box-sizing: border-box;width: 100px;font-weight: bold;">阶段所属学科</span>
          <el-select v-model="form.subjectId" clearable placeholder="请选择学科">
            <el-option
              v-for="item in valsubjectlists"
              :key="item.name"
              :label="item.name"
              :value="item.id">
              <span>{{ item.name }}</span>
            </el-option>
          </el-select>
        </div>
        <!--开始选择-->
        <div style="margin-bottom: 22px;">
          <span style="font-size: 14px;text-align: right;color: #606266;line-height: 40px;padding: 0 12px 0 25px; box-sizing: border-box;width: 200px;font-weight: bold;">课程阶段</span>
          <el-select v-model="knowId" clearable filterable placeholder="请选择课程阶段!" @change="changeValue" >
            <el-option
              v-for="item in courseAllList"
              :key="item.name"
              :label="item.name"
              :value="item.id">
              <span>{{ item.name | unescape }}</span>
            </el-option>
          </el-select>
        </div>
        <!--选中知识点展示列表-->
        <div style="margin-bottom: 22px;display: flex;">
          <p style="font-size: 14px;width: 103px;padding: 0; text-align: right;color: #606266;line-height: 40px;padding: 0 12px 0 0; box-sizing: border-box;width: 100px;font-weight: bold;">课程阶段列表</p>
          <!--<el-input :disabled="true" v-model="qian" type="textarea" placeholder="课程阶段列表" >{{ ming }}</el-input>-->
          <div style="width: 400px;border: 1px solid #ccc;border-radius: 5px;padding: 3px 5px 5px 3px;">
            <el-tag
              v-for="tag in tags"
              :key="tag.name"
              type="success"
              style="margin-top: 5px;margin-left: 5px;"
              closable
              size="mini"
              @close="handleClose(tag)">
              {{ tag.name }}
            </el-tag>
          </div>
        </div>
        <!--展示结束-->
        <el-form-item label="课程状态" prop="status">
          <el-select v-model="form.status" placeholder="请选择课程状态">
            <el-option v-for="item in statusOption" :key="item.value" :label="item.name" :value="item.value">{{ item.name }}</el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button v-if="dialogStatus==='create'" type="primary" @click="send()">确 定</el-button>
        <el-button v-else type="primary" @click="addition">确 定</el-button>
        <el-button @click="cancelD">取 消</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { subjectList, valsubjectList } from '../../api/form'
import { courseList } from '../../api/tree'
import { courseListss, addcourses, editCourses, deleteCourses } from '../../api/table'
// import { unescape } from '../../filters'
import { parseTime } from '../../filters'

export default {

  data() {
    return {
      tags: [],
      qian: '',
      knowId: null,
      filterText: '',
      data2: [{
        id: 1,
        label: 'Level one 1'
      }],
      ming: '',
      subjectList: [],
      valssubjectList: [],
      valsubjectlists: [],
      pageListSubjectid: null,
      pageList: {
        subjectId: null,
        // name: undefined,
        page: 1,
        pageSize: 5
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
      subjectListName: '',
      visitKnow: false,
      textMap: {
        updata: '编辑',
        create: '添加'
      },
      dialogStatus: '',
      form: {
        name: '课程1',
        status: 1,
        subjectId: null,
        courseStageIds: [],
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
      courseAllList: [],
      courseTableList: [],
      subjectlistOld: [],
      yu: ''
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
      this.valsubjectlists = result.data
    })
    // 获取所有课程阶段
    courseList().then((result) => {
      this.courseAllList = result.data.courseStageList
    }).catch(() => {})
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
    cancelD() {
      this.getList()
      this.visitKnow = false
    },
    retrubSub(row) {
      return row.join(',  ')
    },
    handleClose(tag) {
      this.courseAllList.push(tag)
      this.tags.splice(this.tags.indexOf(tag), 1)
    },
    changeValue() {
    //  添加标签
      if (this.knowId) {
        for (let i = 0; i < this.courseAllList.length; i++) {
          if (parseInt(this.courseAllList[i].id) === parseInt(this.knowId)) {
            this.knowId = null
            this.tags.push(this.courseAllList[i])
            this.courseAllList.splice(this.courseAllList.indexOf(this.courseAllList[i]), 1)
          }
        }
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
    edit(row) {
      //  编辑知识点
      this.resetFrom()
      // 获取所有课程阶段
      courseList().then((result) => {
        this.courseAllList = result.data.courseStageList
      }).catch(() => {})
      this.visitKnow = true
      this.dialogStatus = 'updata'
      this.form.name = unescape(row.name)
      this.form.status = row.status
      this.form.subjectId = row.subject_id
      this.form.courseStageIds = row.course_stage_ids
      for (var i = 0; i < this.form.courseStageIds.length; i++) {
        for (let j = 0; j < this.courseAllList.length; j++) {
          if (parseInt(this.form.courseStageIds[i]) === parseInt(this.courseAllList[j].id)) {
            this.tags.push(this.courseAllList[j])
            this.courseAllList.splice(this.courseAllList.indexOf(this.courseAllList[j]), 1)
          }
        }
      }
      this.form.id = row.id
    },
    deleteKnow(id) {
      //  删除知识点
      this.delId.id = id
      this.$confirm('此操作将永久删除该文件, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        deleteCourses(this.delId).then(() => {
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
      for (let b = 0; b < this.subjectList.length; b++) {
        if (row.subject_id === parseInt(this.subjectList[b].id)) {
          return this.subjectList[b].name
        }
      }
    },
    seachWay() {
      if (this.pageListSubjectid === '') {
        this.pageList.subjectId = null
        this.pageList.subjectId = 0
      } else {
        this.pageList.subjectId = this.pageListSubjectid
      }
      if (this.subjectListName) {
        this.pageList.name = this.subjectListName
      } else {
        this.pageList.name = undefined
      }
      this.getList()
    },
    addition() {
      this.form.courseStageIds = []
      for (let a = 0; a < this.tags.length; a++) {
        this.form.courseStageIds.push(parseInt(this.tags[a].id))
      }
      // 修改提交按钮
      editCourses(this.form).then(() => {
        this.visitKnow = false
        this.getList()
      }).catch(() => {
        this.$notify({ title: '失败', message: '修改知识点失败！', type: 'error', duration: 2000 })
      })
    },
    send() {
      // 添加知识点
      this.form.courseStageIds = []
      for (let a = 0; a < this.tags.length; a++) {
        this.form.courseStageIds.push(parseInt(this.tags[a].id))
      }
      addcourses(this.form).then(() => {
        this.visitKnow = false
        this.getList()
      }).catch(() => {
      })
    },
    handleCheckedCitiesChange(value) {
      this.checkedCount = value.length
      // this.checkAll = checkedCount === this.cities.length;
      // this.isIndeterminate = checkedCount > 0 && checkedCount < this.cities.length;
      this.form.subjectIds = value
    },
    filterNode(value, data) {
      if (!value) return true
      return data.label.indexOf(value) !== -1
    },
    getList() {
      courseListss(this.pageList).then((result) => {
        this.totalcount = result.data.totalCount
        this.courseTableList = result.data.courseList
      }).catch((result) => {
        this.errortxt = result.response.data.msg
        this.$notify({ title: '失败', message: this.errortxt, type: 'error', duration: 2000 })
      })
      // 获取所有课程阶段
      courseList().then((result) => {
        this.courseAllList = result.data.courseStageList
      }).catch(() => {})
    },
    append() {
      this.resetFrom()
      this.visitKnow = true
      this.dialogStatus = 'create'
      this.tags = []
    },
    resetFrom() {
      this.form.id = null
      this.form.name = ''
      this.form.subjectId = null
      this.form.courseStageIds = []
      this.form.status = 1
      this.tags = []
    }
  }
}
</script>
<style>
  .el-tag + .el-tag {
    margin-left: 10px;
  }
  .button-new-tag {
    margin-left: 10px;
    height: 32px;
    line-height: 30px;
    padding-top: 0;
    padding-bottom: 0;
  }
  .input-new-tag {
    width: 90px;
    margin-left: 10px;
    vertical-align: bottom;
  }
</style>

