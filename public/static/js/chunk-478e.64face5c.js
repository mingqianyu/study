(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-478e"],{QcTS:function(e,t,n){"use strict";var o=n("rhbk");n.n(o).a},b5jm:function(e,t,n){"use strict";n.d(t,"a",function(){return a});var o=n("t3Un");function a(e){return Object(o.a)({url:"/choiceRole",method:"post",data:e})}},c11S:function(e,t,n){"use strict";var o=n("gTgX");n.n(o).a},gTgX:function(e,t,n){},ntYl:function(e,t,n){"use strict";n.r(t);var o=n("Yfch"),a=n("X4fA"),s=n("oYx3"),i=n("b5jm"),r={name:"Login",data:function(){return{loginForm:{phone:"",password:"",code:""},loginRules:{phone:[{required:!0,trigger:"blur",validator:function(e,t,n){Object(o.a)(t)?n():n(new Error("请输入正确的用户名"))}}],password:[{required:!0,trigger:"blur",validator:function(e,t,n){t.length<5?n(new Error("密码不能小于5位")):n()}}]},loading:!1,pwdType:"password",redirect:void 0,centerDialogVisible:!1,newshenfen:[],id:null,chooseData:{name:"",userId:"",institutionId:"",role:"",phone:""},imgSrc:"",captchaTag:0}},watch:{$route:{handler:function(e){this.redirect=e.query&&e.query.redirect},immediate:!0}},created:function(){this.getCode()},methods:{showPwd:function(){"password"===this.pwdType?this.pwdType="":this.pwdType="password"},getCode:function(){this.captchaTag+=1,this.imgSrc="/verify?"+this.captchaTag},handleLogin:function(){var e=this;this.$refs.loginForm.validate(function(t){if(!t)return console.log("error submit!!"),console.log("请求失败!"),!1;localStorage.setItem("phone",e.loginForm.phone),e.loading=!0,e.$store.dispatch("Login",e.loginForm).then(function(){if(e.loading=!1,e.newshenfen=Object(a.b)(),e.newshenfen=JSON.parse(e.newshenfen),"admin"===e.newshenfen.role){localStorage.setItem("role",e.newshenfen.role),localStorage.setItem("personName",e.newshenfen.name),localStorage.setItem("id",""),e.$router.push({path:"/"});var t=Object(s.a)("admin");e.$router.addRoutes(t),e.$router.options.routes=s.b.concat(t)}e.newshenfen.institutions.length>1?e.$router.push({path:"/choose/index"}):1===e.newshenfen.institutions.length&&(e.chooseData.name=e.newshenfen.name,e.chooseData.userId=e.newshenfen.institutions[0].user_id,e.chooseData.institutionId=e.newshenfen.institutions[0].institutions_id,e.chooseData.role=e.newshenfen.institutions[0].role,e.chooseData.phone=e.loginForm.phone,Object(i.a)(e.chooseData).then(function(t){localStorage.setItem("personName",t.data.name),localStorage.setItem("role",t.data.role);var n=Object(s.a)(t.data.role);e.$router.addRoutes(n),e.$router.options.routes=s.b.concat(n),Object(a.i)(t.data),Object(a.h)(t.data.token),e.$router.push({path:"/"})}).catch(function(){}))}).catch(function(){e.getCode(),e.loading=!1})})}}},c=(n("c11S"),n("QcTS"),n("KHd+")),l=Object(c.a)(r,function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("div",{staticClass:"login-container"},[n("el-form",{ref:"loginForm",staticClass:"login-form",attrs:{model:e.loginForm,rules:e.loginRules,"auto-complete":"on","label-position":"left"}},[n("h3",{staticClass:"title"},[e._v("考试系统")]),e._v(" "),n("el-form-item",{attrs:{prop:"phone"}},[n("span",{staticClass:"svg-container"},[n("svg-icon",{attrs:{"icon-class":"user"}})],1),e._v(" "),n("el-input",{attrs:{name:"phone",type:"text","auto-complete":"on",placeholder:"请输入用户名"},model:{value:e.loginForm.phone,callback:function(t){e.$set(e.loginForm,"phone",t)},expression:"loginForm.phone"}})],1),e._v(" "),n("el-form-item",{attrs:{prop:"password"}},[n("span",{staticClass:"svg-container"},[n("svg-icon",{attrs:{"icon-class":"password"}})],1),e._v(" "),n("el-input",{attrs:{type:e.pwdType,name:"password","auto-complete":"on",placeholder:"请输入密码"},nativeOn:{keyup:function(t){return"button"in t||!e._k(t.keyCode,"enter",13,t.key,"Enter")?e.handleLogin(t):null}},model:{value:e.loginForm.password,callback:function(t){e.$set(e.loginForm,"password",t)},expression:"loginForm.password"}}),e._v(" "),n("span",{staticClass:"show-pwd",on:{click:e.showPwd}},[n("svg-icon",{attrs:{"icon-class":"eye"}})],1)],1),e._v(" "),n("el-form-item",{attrs:{prop:"code"}},[n("span",{staticClass:"svg-container"},[n("svg-icon",{attrs:{"icon-class":"check"}})],1),e._v(" "),n("el-input",{attrs:{type:"text",clearable:"",placeholder:"请输入验证码"},model:{value:e.loginForm.code,callback:function(t){e.$set(e.loginForm,"code",t)},expression:"loginForm.code"}}),e._v(" "),n("img",{staticClass:"check",attrs:{src:e.imgSrc},on:{click:e.getCode}})],1),e._v(" "),n("el-form-item",[n("el-button",{staticStyle:{width:"100%"},attrs:{loading:e.loading,type:"primary"},nativeOn:{click:function(t){return t.preventDefault(),e.handleLogin(t)}}},[e._v("\n        登 录\n      ")])],1)],1)],1)},[],!1,null,"10eac7f4",null);l.options.__file="index.vue";t.default=l.exports},rhbk:function(e,t,n){}}]);