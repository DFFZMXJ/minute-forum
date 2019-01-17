<?php
/*This file is the base file of forum*/
//In fact its name should be "initialize.php" but I am lazy to change.
require "markdown.php";
require "sqlite.php";
/**
 * Property file manager. Store, change, create and delete data.
 */
class Property{
	public static $property_file = "properties.php";
	public static $properties = null;
	public static function read(){
		return self::$properties=require self::$property_file;
	}
	public static function save($property=null){
		if(empty($property))
			if(empty(self::$properties))
				return false;
			else
				$property = self::$properties;
		return file_put_contents(self::$property_file,'<?php '.PHP_EOL.'return '.var_export($property,true).';');
	}
}
if(empty($__DONT_REQUIRE_PROPERTIES__)) Property::read();
//error_reporting(E_ALL ^ E_WARNING);//Disable warnings.
if(empty($__DONT_LAUNCH_DATABASE__)) Database::launch(Property::$properties['database']['location']);
class User{
	public static function logged(){
		//Check login status
		if(empty($_COOKIE['m_token'])) return false;
		return Database::query((new SQL)->select('*')->from('users')->where('token=',SQLite3::escapeString($_COOKIE['m_token']))->getSql());
	}
	public static function login($username,$password,$cookie_remember=true){
		$us = Database::select('users','username',$username);
		if(!$us) return [
			'logged'=>false,
			'message'=>'User does not exist.'
		];
		$us=$us[0];
		if($us['password']==sha1($password)){
			if($cookie_remember) setrawcookie('m_token',$us['token'],time()+31536000,"/");
			return [
				'logged'=>true,
				'message'=>'Signed in successful!'
			];
		}else return [
			'logged'=>false,
			'message'=>'Password incorrect.'
		];
	}
	public static function register($username,$password,$gender){
		if(empty($gender)||!array_search($gender,['male','female','other'])) return [
			'registered'=>false,
			'message'=>'Invailed gender!'
		];
		if(Database::select('users','username',$username)) return [
			'registered'=>false,
			'message'=>'User already exists!'
		];
		if(!preg_match("/^[a-zA-Z_-]{2,16}$/ig")) return [
			'registered'=>false,
			'message'=>'Username only allowed characters,number and - and length between 4 and 16.'
		];
		Database::insert('users',[
			//'userid'=>generate_guid(),
			'username'=>$username,
			'password'=>sha1($password),
			'vip'=>false,
			'gender'=>$gender,
			'token'=>sha1('{'.$username.'-'.mt_rand().'-'.time().'}'),
			'registered'=>time()
		]);
		return [
			'registered'=>true,
			'message'=>'Signed up successful!'
		];
	}
	public static function logout(){
		if(!User::logged()) return [
			'logged'=>false,
			'message'=>'You are not signed in!'
		]; else {
			setrawcookie('m_token','{undefined}',0,"/");
			return [
				'logged'=>true,
				'message'=>'Signed out successful!'
			];
		}
	}
}
class Post{
	public static function create($title,$content){
		/*Submit a new post.*/
		if(!User::logged()) return [
			'created'=>false,
			'message'=>'You are not signed in!'
		];
		if(empty($title)||empty($content)) return [
			'created'=>false,
			'message'=>'Title and content cannot be empty!'
		];
		if(strlen($title)>60) return [
			'created'=>false,
			'message'=>'Maximun of title is 60 characters.'
		];
		$post = Database::insert('posts',[
			'postid'=>Database::select('sqlite_sequence','name','posts')[0]['seq']+1,//To prevent bugs, I have to do this!
			'title'=>SQLite3::escapeString($title),
			'content'=>SQLite3::escapeString($content),
			'user'=>User::logged()['userid'],
			'sticky'=>false,
			'datetime'=>time(),
			'views'=>0,
			'likes'=>'[]',
			'replies'=>0
		]);
		if(!$post) return [
			'created'=>false,
			'message'=>'Internal server error!'
		];
		return [
			'created'=>true,
			'message'=>'Created!',
			'id'=>$post['postid']
		];
	}
	public static function reply($post,$content,$replied_to=null){
		/*Reply a post or someone.*/
		if(!User::logged()) return [
			'replied'=>false,
			'message'=>'You are not signed in!'
		];
		if(empty($post)||empty($content)) return [
			'replied'=>false,
			'message'=>'Post name and content cannot be empty!'
		];
		if(!Database::select('posts','postid',$post)) return [
			'replied'=>false,
			'message'=>'Post not exists!'
		];
		//$rawpost = Database::select('posts','postid',$post)[0];
		//Database::update('posts',Database::select('posts','postid',$rawpost['postid'])[0],$rawpost);
		$rawpost = Database::query((new SQL)->select('*')->from('posts')->where('postid=',$post)->getSql());
		Database::execute((new SQL)->update('posts')->set('replies',$rawpost['replies']+1)->where('postid=',$post)->getSql());
		Database::execute(
			$sql=(new SQL)->insert([[
				'replyid'=>Database::select('sqlite_sequence','name','replies')[0]['seq']+1,
				'post'=>$post,
				'content'=>SQLite3::escapeString($content),
				'user'=>User::logged()['userid'],
				'datetime'=>time(),
				'likes'=>'[]',
				'floor'=>$rawpost['replies']+2,
				'repliedTo'=>$replied_to?SQLite3::escapeString(json_encode([
					'user'=>$replied_to['user'],
					'floor'=>$replied_to['floor']
				])):null
			]])->into('replies')->getSql()
		);
		$reply = [
			'replyid'=>Database::select('sqlite_sequence','name','replies')[0]['seq'],
			'post'=>$post,
			'content'=>SQLite3::escapeString($content),
			'user'=>User::logged()['userid'],
			'datetime'=>time(),
			'likes'=>'[]',
			'floor'=>$rawpost['replies']+2,
			'repliedTo'=>$replied_to?SQLite3::escapeString(json_encode([
				'user'=>$replied_to['user'],
				'floor'=>$replied_to['floor']
			])):null
		];
		if($replied_to){
			$r_user = Database::select('users','userid',$replied_to['user']);
			$r_user = $r_user?$r_user[0]:[
				'userid'=>404,
				'username'=>'User Not Exists',
				'vip'=>false,
				'gender'=>'other'
			];
		}else $r_user=null;
		$markdown = new HyperDown\Parser;
		return [
			'replied'=>true,
			'message'=>'Replied!',
			'replied_to'=>$r_user,
			'reply_floor'=>$reply['floor'],
			'marked_content'=>$markdown->makeHtml($reply['content']),
			'reply'=>$reply['replyid']
		];
	}
	public static function like($type,$id){
		/*Like/Unlike a post/reply.*/
		if(!($u=User::logged())) return [
			'liked'=>false,
			'message'=>'You are not signed in!'
		];
		if(empty($type)) return [
			'liked'=>false,
			'message'=>'Empty type of like!'
		];
		$liked=false;
		switch(strtolower($type)){
			case "post":
				$post = Database::query((new SQL)->select('*')->from('posts')->where('postid=',$id)->getSql());
				if(!$post) return [
					'liked'=>false,
					'message'=>'Post does not exist!'
				];
				$likes = json_decode($post['likes'],true);
				if(array_search($u['userid'],$likes)===false){
					array_push($likes,$u['userid']);
					$liked=true;
				}else foreach($likes as $k=>$v) if($v==$u['userid']) array_splice($likes,$k,1);
				Database::execute((new SQL)->update('posts')->where('postid=',$id)->set('likes',json_encode($likes))->getSql());
				return [
					'liked'=>true,
					'message'=>$liked?'Liked!':'Unliked!',
					'currentStatus'=>$liked
				];
			case "reply":
				$reply = Database::query((new SQL)->select('*')->from('replies')->where('replyid=',$id)->getSql());
				if(!$reply) return [
					'liked'=>false,
					'message'=>'Reply does not exist!'
				];
				$likes = json_decode($reply['likes'],true);
				if(array_search($u['userid'],$likes)===false){
					array_push($likes,$u['userid']);
					$liked = true;
				}else 
					foreach($likes as $k=>$v) if($v==$u['userid']) array_splice($likes,$k,1);
				Database::execute(
					(new SQL)->update('replies')->where('replyid=',$id)->set('likes',json_encode($likes))->getSql()
				);
				return [
					'liked'=>true,
					'message'=>$liked?'Liked!':'Unliked!',
					'currentStatus'=>$liked
				];
				break;
			default:
				return [
					'liked'=>false,
					'message'=>'Unknown type of like!'
				];
				break;
		}
		return [
			'liked'=>false,
			'message'=>'Unknown error occured!'
		];
	}
}
function generate_guid() {
	/*GUID is the best way to define the ID of objects for now.*/
	$charid = strtoupper(md5(uniqid(mt_rand(), true)));
	$hyphen = chr(45);
	$uuid = chr(123)
	.substr($charid, 0, 8).$hyphen
	.substr($charid, 8, 4).$hyphen
	.substr($charid,12, 4).$hyphen
	.substr($charid,16, 4).$hyphen
	.substr($charid,20,12)
	.chr(125);
	return $uuid;
}