<?php

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class HelpCommand extends Command {
    /**
     * @var string Command Name
     */
    protected $name = "help";

    /**
     * @var string Command Description
     */
    protected $description = "获取帮助 | Commands Help";

    /**
     * @inheritdoc
     */
    public function handle($arguments) {
        $commands = $this->getTelegram()->getCommands();
        $response = '';
        $ChatType = $this->getUpdate()->getMessage()->getChat()->getType();
        if ($ChatType != 'private') {
            return;
        }
        foreach ($commands as $name => $command) {
            if ($name == 'gohome') continue;
            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
        }
        $this->replyWithMessage(['text' => $response]);
    }
}
class BindCommand extends Command {
    /**
     * @var string Command Name
     */
    protected $name = "bind";

    /**
     * @var string Command Description
     */
    protected $description = "通过你的 " . CONFIG['SITE_NAME'] . " 用户名和种子密钥末 8 位与 TG 账号绑定 | Bind your account with your USERNAME & TORRENT PASSKEY (last 8 characters)";

    /**
     * @inheritdoc
     */
    public function handle($arguments) {
        $ChatType = $this->getUpdate()->getMessage()->getChat()->getType();
        if ($ChatType != 'private') {
            return;
        }
        $UserTGID = $this->getUpdate()->getMessage()->getFrom()->getId();
        if ($arguments == "") {
            G::$DB->query("select count(1), Username from users_info i left join users_main m on m.id=i.userid where TGID='$UserTGID'");
            list($HasUser, $Username) = G::$DB->next_record();
            if ($HasUser) {
                $this->replyWithMessage(['text' => "已绑定用户 $Username | Have bound with $Username"]);
            } else {
                $this->replyWithMessage(['text' => "请输入 “/bind 用户名 种子密钥末8位” 以完成绑定（密钥不是密码！）| Please enter \"/bind Username Passkey(last 8 characters)\" to bind"]);
            }
            return;
        }
        list($Username, $Passkey) = explode(' ', $arguments);
        $Username = db_string($Username);
        $Passkey = db_string($Passkey);
        if ($Passkey == "") {
            $this->replyWithMessage(['text' => "失败：命令格式错误 | Fail: wrong format \n/bind Username Passkey(last 8 characters)"]);
            return;
        }
        G::$DB->query("select count(1), ID from users_main where username='$Username' and right(torrent_pass, 8)='$Passkey'");
        list($HasUser, $UserID) = G::$DB->next_record();
        if ($HasUser) {
            G::$DB->query("update users_info set TGID='$UserTGID' where UserID=$UserID");
            G::$Cache->delete_value("user_info_heavy_$UserID");
            $this->replyWithMessage(['text' => "成功！| Success!"]);
        } else {
            $this->replyWithMessage(['text' => "失败：用户名或种子密钥有误 | Fail: wrong username or passkey"]);
        }
    }
}
class LoginCommand extends Command {
    /**
     * @var string Command Name
     */
    protected $name = "login";

    /**
     * @var string Command Description
     */
    protected $description = "获取临时登录链接 | Get the temporary login link";

    /**
     * @inheritdoc
     */
    public function handle($arguments) {
        $ChatType = $this->getUpdate()->getMessage()->getChat()->getType();
        if ($ChatType != 'private') {
            return;
        }
        $UserTGID = $this->getUpdate()->getMessage()->getFrom()->getId();

        G::$DB->query("select count(1), Username, ID from users_info i left join users_main m on m.id=i.userid where TGID='$UserTGID'");
        list($HasUser, $Username, $UserID) = G::$DB->next_record();
        if ($HasUser) {
            if (CONFIG['CLOSE_LOGIN']) {
                G::$DB->query("select count(1), LoginKey from login_link where UserID='$UserID' and used='0'");
                list($HasKey, $Key) = G::$DB->next_record();
                if (!$HasKey) {
                    $Key = Users::make_secret();
                    G::$DB->query("insert into login_link (LoginKey, UserID, Username) values ('" . db_string($Key) . "', '$UserID', '$Username')");
                }
                $this->replyWithMessage(['text' => CONFIG['SITE_URL'] . "/login.php?loginkey=$Key"]);
            } else {
                $this->replyWithMessage(['text' => "现在可正常访问登录页面 | You can access to the login page directly"]);
            }
        } else {
            $this->replyWithMessage(['text' => "请输入 “/bind 用户名 种子密钥末8位” 以完成绑定（密钥不是密码！）| Please enter \"/bind Username Passkey(last 8 characters)\" to bind"]);
        }
    }
}
class GoHomeCommand extends Command {
    /**
     * @var string Command Name
     */
    protected $name = "gohome";

    /**
     * @var string Command Description
     */
    protected $description = "与机器人交互 | Chat with the bot";

    /**
     * @inheritdoc
     */
    public function handle($arguments) {
        $ChatType = $this->getUpdate()->getMessage()->getChat()->getType();
        if ($ChatType != 'private') {
            $this->replyWithMessage(['text' => "Come <a href=\"https://t.me/DICGate_Bot
            \">here</a> please~", 'reply_to_message_id' => $this->getUpdate()->getMessage()->getMessageId(), 'parse_mode' => 'HTML']);
        } else {
            $this->replyWithMessage(['text' => "你，到家了～请输入 “/help” 获取更多命令说明 | You are at home now, please enter \"/help\" to get more instructions"]);
        }
    }
}
