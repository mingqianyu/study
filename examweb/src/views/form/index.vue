<template>
  <div class="app-container">
    <el-button type="success" style="margin-left: 2.5%;margin-bottom: 1%;" @click="add">添加学科</el-button>
    <el-table :stripe="true" :data="subjectListData" border="" style="width: 85%;margin-left: 2.5%" size="medium" align="center">
      <el-table-column height="20px" prop="id" label="学科ID" align="center" width="150" >{{ merss }}</el-table-column>
      <el-table-column prop="name" label="学科名称" align="center"><span>{{ merss }}</span></el-table-column>
      <el-table-column prop="code" label="学科代码" align="center"><span>{{ merss }}</span></el-table-column>
      <el-table-column prop="status" label="学科状态" align="center">
        <template slot-scope="scope">
          <span>{{ scope.row.status === 0 ? '无效' : '有效' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center" width="300">
        <template slot-scope="scope" >
          <el-button type="primary" size="small" plain @click="edit(scope.row)">编辑</el-button>
          <el-button type="warning" size="small" plain @click="deleteSubject(scope.row.id)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog :title="textMap[dialogStatus]" :visible.sync="subjecForm" style="width: 60%;min-width: 1100px;margin: 0 auto">
      <el-form :model="form" label-width="100px" style="width: 600px">
        <el-form-item label="学科名称" prop="name" style="width: 400px">
          <el-input v-model="form.name">{{ merss }}</el-input>
        </el-form-item>
        <el-form-item label="学科代码" prop="code" style="width: 400px">
          <el-input v-model="form.code">{{ merss }}</el-input>
        </el-form-item>
        <el-form-item label="学科状态" prop="status">
          <el-select v-model="form.status" placeholder="请选择学科状态">
            <el-option v-for="item in statusOption" :key="item.value" :label="item.name" :value="item.value">{{ item.name }}</el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button v-if="dialogStatus==='create'" type="primary" @click="send()">确 定</el-button>
        <el-button v-else type="primary" @click="addition">确 定</el-button>
        <el-button @click="subjecForm = false">取 消</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { subjectList, addubject, shanchu, editSubject } from '../../api/form'

export default {
  data() {
    return {
      subjectListData: [],
      merss: '',
      subjecForm: false,
      textMap: {
        updata: '编辑',
        create: '添加'
      },
      dialogStatus: '',
      form: {
        name: '',
        code: '',
        status: 1,
        id: null
      },
      rules: {
        name: [
          { required: true, message: '请输入学科名称', trigger: 'blur' },
          { min: 1, max: 20, message: '长度在1到20个字符', trigger: 'blur' }
        ]
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
      iddata: {
        id: ''
      },
      checkoutFrom: {}
    }
  },
  created() {
    this.getList()
  },
  methods: {
    deleteSubject(id) {
      this.iddata.id = id
      // console.log(this.iddata)
      this.$confirm('此操作将永久删除该文件, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        shanchu(this.iddata).then(() => {
          this.$message({
            type: 'success',
            message: '删除成功!'
          })
          this.getList()
        }).catch(() => {
          this.$notify({ title: '失败', message: '删除学科失败！', type: 'error', duration: 2000 })
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },
    edit(row) {
      this.subjecForm = true
      this.dialogStatus = 'updata'
      this.form.name = row.name
      this.form.code = row.code
      this.form.status = row.status
      this.form.id = row.id
      this.checkoutFrom = Object.assign({}, row)
    },
    addition() {
      if (this.form.name === this.checkoutFrom.name && this.form.code === this.checkoutFrom.code && this.form.status === this.checkoutFrom.status) {
        this.subjecForm = false
      } else {
        editSubject(this.form).then(() => {
          this.subjecForm = false
          this.getList()
        }).catch((error) => {
          this.$notify({ title: '失败', message: error.response.data.msg, type: 'error', duration: 2000 })
        })
      }
    },
    add() {
      this.resForm()
      this.subjecForm = true
      this.dialogStatus = 'create'
    },
    resForm() {
      this.form.name = ''
      this.form.code = null
      this.form.status = 1
      this.form.id = null
    },
    getList() {
      subjectList().then((result) => {
        this.subjectListData = result.data
      })
    },
    send() {
      // console.log(this.form)
      addubject(this.form).then((result) => {
        // console.log(result.data)
        this.getList()
        this.subjecForm = false
      }).catch((error) => {
        this.$notify({ title: '添加学科失败', message: error.response.data.msg, type: 'error', duration: 2000 })
      })
    }
  }
}
</script>

<style scoped>
.line{
  text-align: center;
}
</style>

