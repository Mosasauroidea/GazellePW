<?
if (!isset($_GET['token']) || $_GET['token'] != "AAG-CBwj8rntygHG2ODlfLCaq-FLVeXQwdI") {
    error(403);
}

use Telegram\Bot\Api;

include('command.php');
$telegram = new Api('1257614392:AAG-CBwj8rntygHG2ODlfLCaq-FLVeXQwdI');
//$telegram->addCommand(StartCommand::class);
$telegram->addCommands([
    HelpCommand::class,
    BindCommand::class,
    LoginCommand::class,
    GoHomeCommand::class,
]);
$telegram->commandsHandler(true);
