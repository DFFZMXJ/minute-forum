<?php
/**
 * Access this page to setup your forum.
 * e.g. https://127.0.0.1/forum/setup.php?safelock=unlocked&forumname=5%20Minute%20Forum&database=storage/sqlite.db&username=DFFZMXJ&password=123456&gender=female
 * Properties:
 * * safeblock: You must set this as true before installing
 * * forumname: Forum name
 * * database: Location of database
 * * username
 * * password
 * * gender
 */
$__DONT_LAUNCH_DATABASE__=true;
$__DONT_REQUIRE_PROPERTIES__=true;
require "intialize.php";
if(file_exists(Property::$property_file)) die("You've already setup 5 Minute Forum. Please use and enjoy it! If you have any problems, please contact the developer.");
if(empty($_GET['safelock'])){
    header("HTTP/1.1 401 Unauthorized");
    require "setup.html";
    exit();
}
if(empty($_GET['safelock'])||empty($_GET['forumname'])||empty($_GET['database'])||empty($_GET['username'])||empty($_GET['password'])||empty($_GET['gender'])){
    $__SETUP_ERROR__ = "All data is required.";
    header("HTTP/1.1 406 Unacceptable");
    require "setup.html";
    exit();
}
//var_export
file_put_contents(Property::$property_file,'<?php '.PHP_EOL.'return '.var_export([
    //Write configurations into assigned file.
    'database'=>[
        'type'=>'sqlite',//Feature in future: Multi-database support.
        'location'=>$_GET['database']
    ],
    'console_password'=>sha1($_GET['password']),//Password to login into console.
    'secure_key'=>sha1(uniqid($_GET['password'],true)),//Secure key to verify status.
    'forum'=>[
        'name'=>$_GET['forumname'],//Name of forum
        'announcement'=>'Welcome to '.$_GET['forumname'].'! To manage your forum, please visit console.php!'//Announcement of forum
    ]
],true).';');
Database::launch($_GET['database']);
if(!Database::execute(file_get_contents('setup.sql'))) throw Exception("Unable to create tables.");
if(!Database::execute((new SQL)->insert([[
    'username'=>$_GET['username'],
	'password'=>sha1($_GET['password']),
	'vip'=>true,
	'gender'=>$_GET['gender'],
	'token'=>sha1('{FOUNDER-'.$_GET['username'].'-'.sha1(uniqid(mt_rand(),true)).'-'.time().'}'),
	'registered'=>time()
]])->into('users')->getSql())) throw Exception("Unable to create users.");
header("location:./");
echo "Successful!";