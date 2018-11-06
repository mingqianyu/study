<template>
  <div class="app-container">
    <div style="margin-bottom: 20px;">
      <el-select v-model="pageListSubjectid" :onblur="seachWay" clearable placeholder="请选择学科" @change="seachWay">
        <el-option
          v-for="item in subjectList"
          :key="item.name"
          :label="item.name"
          :value="item.id">
          <span>{{ item.name }}</span>
        </el-option>
      </el-select>
      <el-input v-model="subjectListName" :onchange="seachWay" class="title-input" style="width: 200px;height: 40px;" placeholder="请输入知识点名称" clearable @change="seachWay">
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
        label="知识点名称"
        align="center"
        min-width="300">
        <template slot-scope="scope">
          <span>{{ scope.row.name | unescape }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="subject_ids"
        align="center"
        label="所属学科"
        width="100">
        <template slot-scope="scope">
          <span>{{ seachsub(scope.row) }}</span>
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
          <span>{{ nameF(scope.row) }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="edit_time"
        label="编辑时间"
        align="center"
        width="180">
        <template slot-scope="scope">
          <span>{{ scope.row.edit_time | parseTime }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="status"
        label="知识点状态"
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
        <el-form-item label="知识点名称" prop="name" style="width: 400px">
          <el-input v-model="form.name">{{ ming }}</el-input>
        </el-form-item>
        <el-form-item label="所属学科" prop="status">
          <el-checkbox-group v-model="checkedCities" @change="handleCheckedCitiesChange">
            <el-checkbox v-for="city in valssubjectList" :label="city.id" :key="city.name" :value="city.id">{{ city.name }}</el-checkbox>
          </el-checkbox-group>
        </el-form-item>
        <el-form-item label="知识点状态" prop="status">
          <el-select v-model="form.status" placeholder="请选择知识点状态">
            <el-option v-for="item in statusOption" :key="item.value" :label="item.name" :value="item.value">{{ item.name }}</el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button v-if="dialogStatus==='create'" type="primary" @click="send()">确 定</el-button>
        <el-button v-else type="primary" @click="addition">确 定</el-button>
        <el-button @click="visitKnow = false">取 消</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { knowList, addKnow, editknow, shanchu } from '../../api/know'
import { subjectList, valsubjectList } from '../../api/form'
import { unescape } from '../../filters'

export default {

  data() {
    return {
      filterText: '',
      data2: [{
        id: 1,
        label: 'Level one 1'
      }],
      ming: '',
      subjectList: [],
      valssubjectList: [],
      pageListSubjectid: null,
      pageList: {
        subjectId: null,
        name: undefined,
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
        name: '某一门课',
        status: 1,
        subjectIds: [],
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
      errortxt: ''
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
    // 获取所有有效学科
    valsubjectList().then((result) => {
      // console.log(result.data)
      this.valssubjectList = result.data
    }).catch((result) => {
      this.$notify({ title: '失败', message: '加载列表失败！', type: 'error', duration: 2000 })
    })
    this.getList()
  },
  methods: {
    nameF(row) {
      if (row.editName === '') {
        return '无'
      } else {
        return row.editName
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
    //   console.log(row)
      this.resetFrom()
      this.checkedCities = []
      this.visitKnow = true
      this.dialogStatus = 'updata'
      this.form.name = unescape(row.name)
      this.form.status = row.status
      this.form.subjectIds = row.subject_ids
      for (let i = 0; i < row.subject_ids.length; i++) {
        this.checkedCities.push(parseInt(row.subject_ids[i]))
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
        shanchu(this.delId).then(() => {
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
      return sub.substring(0, sub.length - 1)
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
      editknow(this.form).then(() => {
        this.visitKnow = false
        this.getList()
      }).catch(() => {
        this.$notify({ title: '失败', message: '修改知识点失败！', type: 'error', duration: 2000 })
      })
    },
    send() {
    // 添加知识点
      this.form.subjectIds = this.checkedCities
      // console.log(this.form)
      addKnow(this.form).then(() => {
        // console.log('成功添加知识点')
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
      knowList(this.pageList).then((result) => {
        // console.log(result.data)
        this.totalcount = result.data.totalCount
        this.knowlistAll = result.data.knowledgeList
      }).catch((result) => {
        this.errortxt = result.response.data.msg
        this.$notify({ title: '失败', message: this.errortxt, type: 'error', duration: 2000 })
      })
    },
    append() {
      this.resetFrom()
      this.checkedCities = []
      this.visitKnow = true
      this.dialogStatus = 'create'
    },
    resetFrom() {
      this.form.id = null
      this.form.name = ''
      this.form.subjectIds = []
      this.form.status = 1
    }
  }
}
</script>

