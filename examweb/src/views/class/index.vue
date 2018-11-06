<template>
  <div class="dashboard-container">
    <div style="width: 80%;">
      <p style="color: #606266;">当前所有班级: </p>
      <div style="padding-left: 9px;">
        <el-input
          v-model="className"
          placeholder="请输入班级名称,回车进行搜索"
          clearable
          style="width: 240px;"
          @change="getList">
          <!---->
        </el-input>
      </div>
      <div v-for="item in classList" :key="item.id" style="display: inline;">
        <el-button type="primary" plain style="margin-left: 10px;margin-top: 10px;width: 120px;height: 40px;" @click="comein(item)">{{ item.name }}</el-button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import { getData, getchoose, setToken } from '../../utils/auth'
import { getClassList } from '../../api/class'

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
      classListdata: {
        userId: '',
        token: ''
      },
      className: '',
      personImg: '',
      personImg1: '',
      classList: [
        { name: '班级未加载!!' }
      ]
    }
  },
  computed: {
    ...mapGetters([
      'name',
      'roles'
    ])
  },
  created() {
    this.userXin = getData()
    this.personImg = getchoose()
    this.personImg1 = JSON.parse(this.personImg)
    this.getList()
  },
  methods: {
    data() {
      this.information = getData()
    },
    getList() {
      this.classListdata.userId = this.personImg1.id
      this.classListdata.token = this.personImg1.token
      setToken(this.personImg1.token)
      getClassList(this.personImg1.id, this.className).then((result) => {
        this.classList = result.data
        // console.log(this.classList)
      }).catch(() => {
      })
    },
    comein1(row) {
      // console.log(row)
    },
    comein(row) {
      // console.log(row)
      localStorage.setItem('classId', row.id)
      // console.log(localStorage.getItem('classId'))
      localStorage.setItem('subjectId', row.subject_id)
      this.$router.push({ path: '/exam/index' })
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
