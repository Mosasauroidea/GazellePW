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
<div id="invite_mail_background" style="background-image: linear-gradient(#bbbbbc, #c4c4c4);
    padding: 20px;">
<div id="invite_mail_container" style="background-color: #fff;
	border-radius: 5px;
	max-width: 600px;
	margin: 0 auto;
	box-shadow: 0 0 6px 0 rgba(0, 0, 0, .1);">
<div id="invite_mail_head" style="border-top-left-radius: 5px;
    border-top-right-radius: 5px;
	text-align: center;
	padding: 20px 0;
	background-color: #1e2538;">
<img src="{{ SITE_URL}}/static/styles/public/images/loginlogo.png" style="width: 50%;">
</div>
<div id="invite_mail_body" style="padding: 15px;">这封电子邮件用于激活你刚刚在 {{SITE_NAME}} 注册的账号。你需要在 24 小时内激活并登录来完成 {{Username}} 的整个注册流程。<br/>
<p class="button_container" style="text-align: center;
	margin: 10px 0;"><a class="button" target="_blank" href='{{SITE_URL}}/register.php?confirm={{TorrentKey}}' style="cursor: pointer;
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
	font-size: 1.1rem;">激活</a>
<a class="button" target="_blank" href='{{SITE_URL}}/login.php?loginkey={{LoginKey}}' style="cursor: pointer;
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
	font-size: 1.1rem;">登录</a></p>
<span>你的 passkey 末八位是：{{LoginKey}} （将来可用于获取临时登录链接）</span><br/>
<hr/>
<br/>
This email is to confirm the account you just created at {{SITE_NAME}}. You have 24 hours to confirm and login to finish the registration process for the account created with the username: {{Username}}.<br/>
<p class="button_container" style="text-align: center;
	margin: 10px 0;"><a class="button" target="_blank" href='{{SITE_URL}}/register.php?confirm={{TorrentKey}}' style="cursor: pointer;
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
	font-size: 1.1rem;">Confirm</a>
<a class="button" target="_blank" href='{{SITE_URL}}/login.php?loginkey={{LoginKey}}' style="cursor: pointer;
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
	font-size: 1.1rem;">Login</a></p>
<span>The last 8 digits of your passkey are: {{LoginKey}} (can be used to obtain temporary login link in the future)</span><br/>
<br/>
Thank you,<br/>
{{SITE_NAME}} Staff</div>
</div></div>