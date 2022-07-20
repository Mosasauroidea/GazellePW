
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
<img src="{{SITE_URL}}/static/styles/public/images/loginlogo.png" style="width: 50%;">
</div>
<div id="invite_mail_body" style="padding: 15px;">此邮件用于确认对该用户发起的密码重置请求：{{Username}} <br/><br/>
请在 1 小时内点击下方按钮完成密码重置：<br/>
<p class="button_container" style="text-align: center;
	margin: 10px 0;"><a class="button" target="_blank" href='{{SITE_URL}}/login.php?act=recover&key={{ResetKey}}' style="cursor: pointer;
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
	font-size: 1.1rem;">重置</a></p>

本次用户请求重置的 IP 地址为 {{IP}}。<br/><br/>
如果你不需要重置密码，请忽略此邮件。<br/>
<br/>
<hr/>
<br/>
A password reset process has been started for the username: {{Username}}<br/><br/>
To finish this process please click the link below (you have 1 hour):<br/>
<p class="button_container" style="text-align: center;
	margin: 10px 0;"><a class="button" target="_blank" href='{{SITE_URL}}/login.php?act=recover&key={{ResetKey}}' style="cursor: pointer;
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
	font-size: 1.1rem;">Reset</a></p>
The user who requested the password reset had the IP address {{IP}}.<br/><br/>
If you did not initiate this password reset then please disregard this email.<br/><br/><br/>
Thank you,<br/>
{{SITE_NAME}} Staff</div>
</div></div>