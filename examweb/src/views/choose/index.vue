<template style="background:#EBEEF5">
  <div style="padding: 50px 100px;height: 100%;">
    欢迎你: <span style="color: #67C23A;font-weight: bold;">{{ username }}</span>
    <p>请选择登录角色:</p>
    <img style="width: 100px;" src="http://son.jingyingba.com/orjImage/1/relatelogo_1.png" alt="">

    <el-table :data="tableData" stripe style="width: 50%" border>
      <el-table-column align="center" prop="institutions_job" label="身份" >{{ newshenfen }}</el-table-column>
      <el-table-column align="center" prop="institutions_name" label="公司名称" >{{ newshenfen }}</el-table-column>
      <el-table-column align="center" label="操作">
        <template slot-scope="scope">
          <el-button
            size="mini"
            @click="handleEdit(scope.row)">登录</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import { getData } from '../../utils/auth'
import { getNewShenfen } from '../../utils/auth'
import { setchoose, setToken } from '../../utils/auth'
import { chooseLogin } from '../../api/choose'
import { addNewRouter, constantRouterMap } from '@/router'

export default {
  name: 'Dashboard',
  data() {
    return {
      information: '',
      xinxi: {
        name: '',
        token: ''
      },
      userXin: '',
      newshenfen: {},
      tableData: [],
      username: '',
      chooseData: {
        name: '',
        userId: '',
        institutionId: '',
        role: '',
        phone: ''
      },
      chooseloginData: ''
    }
  },
  created() {
    this.getList()
  },
  methods: {
    data() {
      this.information = getData()
      // console.log(this.information)
    },
    getList() {
      this.newshenfen1 = getNewShenfen()
      this.newshenfen = JSON.parse(this.newshenfen1)
      // console.log(this.newshenfen)
      this.username = this.newshenfen.name
      this.tableData = this.newshenfen.institutions
    },
    handleEdit(row) {
      this.chooseData.name = this.username
      this.chooseData.userId = row.user_id
      this.chooseData.institutionId = row.institutions_id
      this.chooseData.role = row.role
      this.chooseData.phone = localStorage.getItem('phone')
      chooseLogin(this.chooseData).then((result) => {
        // console.log(result.data)
        // console.log(result.data.role)
        localStorage.setItem('personName', result.data.name)
        localStorage.setItem('role', result.data.role)
        // console.log(localStorage.getItem('role'))
        this.$router.push({ path: '/' })
        // console.log('是否是学生')
        // console.log(result.data.role === 'student')
        const newRouters = addNewRouter(result.data.role)
        this.$router.addRoutes(newRouters)
        this.$router.options.routes = constantRouterMap.concat(newRouters)
        setchoose(result.data)
        setToken(result.data.token)
      }).catch(() => {
      })
    }
  }
}

</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard {
  &-container {
    margin: 30px;
  }
  &-text {
    font-size: 30px;
    line-height: 46px;
  }
}
</style>
