<template>
  <div class="dashboard-container">
    <img style="width: 100px;" src="http://son.jingyingba.com/orjImage/1/relatelogo_1.png" alt="">
    <div class="dashboard-text">用户名: <span style="color: skyblue;margin-left: 10px;font-family: PingFang SC;"> {{ username.name }}</span></div>
    <div class="dashboard-text">当前角色: <span style="color:#409EFF;margin-left: 10px;">{{ username.role === 'student' ? '学生' : (username.role === 'teacher' ? '老师' : '管理员') }}</span></div>
    <div class="dashboard-text">当前绑定的手机号: <span style="color:#409EFF;margin-left: 10px;">{{ phone }}</span></div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import { getchoose } from '../../utils/auth'

export default {
  name: 'Dashboard',
  data() {
    return {
      username: '',
      username1: '',
      phone: ''
    }
  },
  computed: {
    ...mapGetters([
      'name',
      'roles'
    ])
  },
  created() {
    this.nameList()
  },
  methods: {
    nameList() {
      this.username1 = getchoose()
      // console.log(this.username1)
      this.username = JSON.parse(this.username1)
      // console.log(this.username)
      this.phone = this.username.phone
      if (!this.phone) {
        this.phone = localStorage.getItem('phone')
      }
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
