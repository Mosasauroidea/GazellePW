<?

require('config.php');

set_include_path(CONFIG['SERVER_ROOT']);
require('classes/const.php');
require('classes/classloader.php');
require('classes/debug.class.php'); //Require the debug class
require('classes/mysql.class.php'); //Require the database wrapper
require('classes/cache.class.php'); //Require the caching class
require('classes/time.class.php'); //Require the time class
require('classes/lang.class.php'); //Require the lang class
require('classes/regex.php');
require('classes/util.php');
require('vendor/autoload.php');

$DB = new DB_MYSQL;
$Debug = new DEBUG;
$Debug->handle_errors();
$Cache = new CACHE($CONFIG['MemcachedServers']);

$Twig = new Twig\Environment(
    new Twig\Loader\FilesystemLoader([
        CONFIG['SERVER_ROOT'] . '/templates',
        CONFIG['SERVER_ROOT'] . '/src/locales',
    ]),
    ['debug' => CONFIG['DEBUG_MODE'], 'cache' => CONFIG['SERVER_ROOT'] . '/.cache/twig']
);

ImageTools::init(CONFIG['IMAGE_PROVIDER']);

G::$Cache = &$Cache;
G::$DB = &$DB;
G::$Debug = &$Debug;
G::$Twig = &$Twig;
