<style type="text/css">
#invite_mail_background{
    background-image: linear-gradient(#bbbbbc, #c4c4c4);
    padding: 20px;
}
#invite_mail_container{
	background-color: #fff;
	border-radius: 5px;
	max-width: 600px;
	margin: 0 auto;
	box-shadow: 0 0 6px 0 rgba(0, 0, 0, .1);
}
#invite_mail_head{
	border-top-left-radius: 5px;
    border-top-right-radius: 5px;
	text-align: center;
	padding: 20px 0;
	background-color: #1e2538;
}
#invite_mail_head>img{
	width: 50%;
}
#invite_mail_body{
	padding: 15px;
}
#invite_mail_body>ol{
	padding-top: 0;
	margin-top: 0;
	margin-left: 10px;
	padding-left: 10px;
}
.button_container{
	text-align: center;
	margin: 10px 0;
}
.button{
    cursor: pointer;
    outline: 0;
    transition: all .1s linear;
    background: #4285f4;
    border: none;
    border-radius: 5px;
    box-shadow: 0 0 4px 0 rgba(0, 0, 0, .2);
    color: #ffffff;
    padding: 5px 10px;
    margin: 0px 2px;
	text-decoration: none !important;
	font-size: 1.1rem;
}
.button:hover{
	background: #1958bd;
}
li.important{
	color: #d8210d
}
</style>
<div id="invite_mail_background" style="background-image: linear-gradient(#bbbbbc, #c4c4c4); padding: 20px;">
<div id="invite_mail_container" style="background-color: #fff;border-radius: 5px;max-width: 600px;margin: 0 auto;box-shadow: 0 0 6px 0 rgba(0, 0, 0, .1);">
<div id="invite_mail_head" style="border-top-left-radius: 5px;
border-top-right-radius: 5px;
text-align: center;
padding: 20px 0;
background-color: #1e2538;">
<img src="{{SiteURL}}/static/styles/public/images/loginlogo.png" style="width: 50%;">
</div>
<div id="invite_mail_body" style="padding: 15px;">用户 {{UserName}} 邀请你加入 {{SiteName}} 且指定了你的邮箱地址 {{CurEmail}}。欲确认邀请，请单击下方的按钮：<br/>
<p class="button_container" style="text-align: center;
margin: 10px 0;"><a class="button" target="_blank" href='{{SiteURL}}/register.php?invite={{InviteKey}}' style="cursor: pointer;
outline: 0;
transition: all .1s linear;
background: #4285f4;
border: none;
border-radius: 5px;
box-shadow: 0 0 4px 0 rgba(0, 0, 0, .2);
color: #ffffff;
padding: 5px 10px;
margin: 0px 2px;
text-decoration: none !important;
font-size: 1.1rem;">注册</a></p>
注意事项：
<ol style="padding-top: 0;
margin-top: 0;
margin-left: 10px;
padding-left: 10px">
<li>此邮件的有效期为 72 小时</li>
<li class="important" style="color: #d8210d">一人一生一号。如果你已有账号且无法登录，请加入 <a href="{{TGDisableChannel}}" target="_blank">账号问题咨询群</a>（需挂梯）</li>
<li class="important" style="color: #d8210d">通过交易获取邀请注册的用户会被封禁</li>
<li class="important" style="color: #d8210d">请使用家庭、本地网络注册，使用代理注册的用户会被封禁</li>
<li>如果你对本邮件的内容感到莫名其妙，请无视</li>
</ol>
<hr/>
<br/>
The user {{UserName}} has invited you to join {{SiteName}} and has specified this address ({{CurEmail}}) as your email address. To confirm your invite, click on the following button:<br/>
<p class="button_container" style="text-align: center;
margin: 10px 0;"><a class="button" target="_blank" href='{{SiteURL}}/register.php?invite={{InviteKey}}' style="cursor: pointer;
outline: 0;
transition: all .1s linear;
background: #4285f4;
border: none;
border-radius: 5px;
box-shadow: 0 0 4px 0 rgba(0, 0, 0, .2);
color: #ffffff;
padding: 5px 10px;
margin: 0px 2px;
text-decoration: none !important;
font-size: 1.1rem;">Register</a></p>
Note:
<ol style="padding-top: 0;
margin-top: 0;
margin-left: 10px;
padding-left: 10px">
<li>It will expire if you do not use this invite in the next 72 hours</li>
<li class="important" style="color: #d8210d">One person should NOT create more than one account. If you already had an account and cannot log in, please join <a href="{{TGDisableChannel}}" target="_blank">{{TGDisableChannelName}}</a> for help</li>
<li class="important" style="color: #d8210d">Users who get the account by trade will be disabled</li>
<li class="important" style="color: #d8210d">Please use the home connection to register. Users who register by proxy will be disabled</li>
<li>Please ignore this email if you don't understand</li>
</ol><br/>
Thank you, <br/>
{{SiteName}} Staff</div>
</div></div>