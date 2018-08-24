<?php
/*This is the installer file*/
require "intialize.php";
$task = 0;
Database::create("users"); $task++;
$admin = Database::insert("users",[
	'userid'=>generate_guid(),
	'username'=>'Administrator',
	'password'=>sha1('minute-forum'),
	'vip'=>true,
	'gender'=>'other',
	'token'=>sha1('{'.'Administrator-'.mt_rand().'-'.time().'}'),
	'registered'=>time()
]); $task++;
Database::create("posts"); $task++;
$post = Database::insert("posts",[
	'postid'=>generate_guid(),
	'title'=>'Welcome to 5 minute forum',
	'content'=>'Hi! This is the first post of forum! Delete this post at database `storage/posts.json` and start your administration life!',
	'sticky'=>true,
	'user'=>$admin['userid'],
	'datetime'=>time(),
	'views'=>0,
	'likes'=>[],
	'replies'=>1
]); $task++;
Database::create("replies"); $task++;
Database::insert("replies",[
	'replyid'=>generate_guid(),
	'post'=>$post['postid'],
	'content'=>'This is the example reply :) ',
	'user'=>$admin['userid'],
	'datetime'=>time(),
	'likes'=>[],
	'floor'=>2,
	'repliedTo'=>null
]); $task++;
if($task<4) die(":( An error occured. Delete all files in storage and try again!");
echo "Congratulations! Your forum has been installed successfully! Go to home page to preview!";