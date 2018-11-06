<template>
  <div class="login-container">
    <el-form ref="loginForm" :model="loginForm" :rules="loginRules" class="login-form" auto-complete="on" label-position="left">
      <h3 class="title">考试系统</h3>
      <el-form-item prop="phone">
        <span class="svg-container">
          <svg-icon icon-class="user" />
        </span>
        <el-input v-model="loginForm.phone" name="phone" type="text" auto-complete="on" placeholder="请输入用户名" />
      </el-form-item>
      <el-form-item prop="password">
        <span class="svg-container">
          <svg-icon icon-class="password" />
        </span>
        <el-input
          :type="pwdType"
          v-model="loginForm.password"
          name="password"
          auto-complete="on"
          placeholder="请输入密码"
          @keyup.enter.native="handleLogin" />
        <span class="show-pwd" @click="showPwd">
          <svg-icon icon-class="eye" />
        </span>
      </el-form-item>
      <el-form-item prop="code">
        <span class="svg-container">
          <svg-icon icon-class="check" />
        </span>
        <el-input v-model="loginForm.code" type="text" clearable placeholder="请输入验证码">
          <!--,.,.-->
        </el-input>
        <img :src="imgSrc" class="check" @click="getCode"/>
      </el-form-item>
      <el-form-item>
        <el-button :loading="loading" type="primary" style="width:100%;" @click.native.prevent="handleLogin">
          登 录
        </el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { isvalidUsername } from '@/utils/validate'
import { getNewShenfen } from '../../utils/auth'
import { addNewRouter, constantRouterMap } from '@/router'
import { setchoose, setToken } from '../../utils/auth'
import { chooseLogin } from '../../api/choose'

export default {
  name: 'Login',
  data() {
    const validateUsername = (rule, value, callback) => {
      if (!isvalidUsername(value)) {
        callback(new Error('请输入正确的用户名'))
      } else {
        callback()
      }
    }
    const validatePass = (rule, value, callback) => {
      if (value.length < 5) {
        callback(new Error('密码不能小于5位'))
      } else {
        callback()
      }
    }
    return {
      loginForm: {
        phone: '',
        password: '',
        code: ''
      },
      loginRules: {
        phone: [{ required: true, trigger: 'blur', validator: validateUsername }],
        password: [{ required: true, trigger: 'blur', validator: validatePass }]
      },
      loading: false,
      pwdType: 'password',
      redirect: undefined,
      centerDialogVisible: false,
      newshenfen: [],
      id: null,
      chooseData: {
        name: '',
        userId: '',
        institutionId: '',
        role: '',
        phone: ''
      },
      imgSrc: '',
      captchaTag: 0
    }
  },
  watch: {
    $route: {
      handler: function(route) {
        this.redirect = route.query && route.query.redirect
      },
      immediate: true
    }
  },
  created() {
    this.getCode()
  },
  methods: {
    showPwd() {
      if (this.pwdType === 'password') {
        this.pwdType = ''
      } else {
        this.pwdType = 'password'
      }
    },
    getCode() {
      this.captchaTag += 1
      this.imgSrc = '/verify?' + this.captchaTag
    },
    handleLogin() {
      this.$refs.loginForm.validate(valid => {
        if (valid) {
          // 记录手机号
          localStorage.setItem('phone', this.loginForm.phone)
          this.loading = true
          this.$store.dispatch('Login', this.loginForm).then(() => {
            this.loading = false
            this.newshenfen = getNewShenfen()
            this.newshenfen = JSON.parse(this.newshenfen)
            // console.log(this.newshenfen)
            if (this.newshenfen.role === 'admin') {
              localStorage.setItem('role', this.newshenfen.role)
              localStorage.setItem('personName', this.newshenfen.name)
              // console.log('管理员登录')
              localStorage.setItem('id', '')
              this.$router.push({ path: '/' })
              const newRouters = addNewRouter('admin')
              this.$router.addRoutes(newRouters)
              this.$router.options.routes = constantRouterMap.concat(newRouters)
            }
            if (this.newshenfen.institutions.length > 1) {
              this.$router.push({ path: '/choose/index' })
            } else if (this.newshenfen.institutions.length === 1) {
              this.chooseData.name = this.newshenfen.name
              this.chooseData.userId = this.newshenfen.institutions[0].user_id
              this.chooseData.institutionId = this.newshenfen.institutions[0].institutions_id
              this.chooseData.role = this.newshenfen.institutions[0].role
              this.chooseData.phone = this.loginForm.phone
              chooseLogin(this.chooseData).then((result) => {
                localStorage.setItem('personName', result.data.name)
                localStorage.setItem('role', result.data.role)
                const newRouters = addNewRouter(result.data.role)
                this.$router.addRoutes(newRouters)
                this.$router.options.routes = constantRouterMap.concat(newRouters)
                setchoose(result.data)
                setToken(result.data.token)
                this.$router.push({ path: '/' })
              }).catch(() => {})
            }
          }).catch(() => {
            this.getCode()
            this.loading = false
          })
        } else {
          console.log('error submit!!')
          console.log('请求失败!')
          return false
        }
      })
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
$bg:#2d3a4b;
$light_gray:#eee;

/* reset element-ui css */
.login-container {
  .el-input {
    display: inline-block;
    height: 47px;
    width: 85%;
    input {
      background: transparent;
      border: 0px;
      -webkit-appearance: none;
      border-radius: 0px;
      padding: 12px 5px 12px 15px;
      color: $light_gray;
      height: 47px;
      &:-webkit-autofill {
        -webkit-box-shadow: 0 0 0px 1000px $bg inset !important;
        -webkit-text-fill-color: #fff !important;
      }
    }
  }
  .el-form-item {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    color: #454545;
    .check {
      position: absolute;
      top: 0;
      right: 0;
      height: 52px;
      width: 150px;
      border-radius: 5px;
    }
  }
}

</style>

<style rel="stylesheet/scss" lang="scss" scoped>
$bg:#2d3a4b;
$dark_gray:#889aa4;
$light_gray:#eee;
.login-container {
  position: fixed;
  height: 100%;
  width: 100%;
  background-color: $bg;
  .login-form {
    position: absolute;
    left: 0;
    right: 0;
    width: 520px;
    max-width: 100%;
    padding: 35px 35px 15px 35px;
    margin: 120px auto;
  }
  .tips {
    font-size: 14px;
    color: #fff;
    margin-bottom: 10px;
    span {
      &:first-of-type {
        margin-right: 16px;
      }
    }
  }
  .svg-container {
    padding: 6px 5px 6px 15px;
    color: $dark_gray;
    vertical-align: middle;
    width: 30px;
    display: inline-block;
  }
  .title {
    font-size: 26px;
    font-weight: 400;
    color: $light_gray;
    margin: 0px auto 40px auto;
    text-align: center;
    font-weight: bold;
  }
  .show-pwd {
    position: absolute;
    right: 10px;
    top: 7px;
    font-size: 16px;
    color: $dark_gray;
    cursor: pointer;
    user-select: none;
  }
}
</style>
