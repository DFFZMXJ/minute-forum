<?php
/*This file is the base file of forum*/
require "markdown.php";
$config=[
	/*Settings of forum*/
	'sitename'=>'5 Minute Forum',
	'banner'=>'Sign in with password <code>minute-forum</code> and start talking~'
];
class Database{
	/*JSON database manager*/
	public static function ls($table){
		/*List the contents in table*/
		if(empty($table)) return false;
		if(!file_exists("storage/".$table.".json")) return false;
		$tb = fopen("storage/".$table.".json","r");
		$cache = "";
		while(!feof($tb)) $cache.=fgets($tb);
		fclose($tb);
		return json_decode($cache,true);
	}
	public static function create($table){
		/*Create new table*/
		if(empty($table)||file_exists("storage/".$table.".json")) return false;
		$tb = fopen("storage/".$table.".json","w");
		fwrite($tb,"[]");
		fclose($tb);
		return true;
	}
	public static function drop($table){
		/*Drop a table*/
		if(empty($table)||!file_exists("storage/".$table.".json")) return false;
		unlink("storage/".$table.".json");
		return true;
	}
	public static function insert($table,$column){
		/*Insert new data*/
		if(empty($table)||empty($column)||!file_exists("storage/".$table.".json")) return false;
		$tb = Database::ls($table);
		array_push($tb,$column);
		$fs = fopen("storage/".$table.".json","w");
		fwrite($fs,json_encode($tb));
		fclose($fs);
		return $column;
	}
	public static function update($table,$column,$newcolumn){
		if(empty($table)||empty($column)||empty($newcolumn)||!file_exists("storage/".$table.".json")) return false;
		$tb = Database::ls($table);
		$c = array_search($column,$tb,true);
		$tb[$c]=$newcolumn;
		$fs = fopen("storage/".$table.".json","w");
		fwrite($fs,json_encode($tb));
		fclose($fs);
		return true;
	}
	public static function select($table,$query,$value){
		if(empty($table)) return false;
		$tb = Database::ls($table);
		if(empty($query)&&empty($value)) return $tb;
		$selections = [];
		for($i = 0; $i < count($tb); $i++) if($tb[$i][$query]===$value) array_push($selections,$tb[$i]);
		return count($selections)?$selections:null;
	}
	public static function delete($table,$query,$value){
		/*Delete all selected collections. (Unsupported for now)*/
	}
}
class User{
	public static function logged(){
		if(empty($_COOKIE['m_token'])) return false;
		$us = Database::select('users','token',$_COOKIE['m_token']);
		return $us?$us[0]:false;
	}
	public static function login($username,$password,$cookie_remember=true){
		$us = Database::select('users','username',$username);
		if(!$us) return [
			'logged'=>false,
			'message'=>'User not exist.'
		];
		$us=$us[0];
		if($us['password']===sha1($password)){
			if($cookie_remember) setrawcookie('m_token',$us['token'],time()+2678400,"/");
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
			'userid'=>generate_guid(),
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
		if(count($title)>60) return [
			'created'=>false,
			'message'=>'Maximun of title is 60 characters.'
		];
		$post = Database::insert('posts',[
			'postid'=>generate_guid(),
			'title'=>$title,
			'content'=>$content,
			'user'=>User::logged()['userid'],
			'sticky'=>false,
			'datetime'=>time(),
			'views'=>0,
			'likes'=>[],
			'replies'=>0
		]);
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
		$rawpost = Database::select('posts','postid',$post)[0];
		$rawpost['replies']++;
		Database::update('posts',Database::select('posts','postid',$rawpost['postid'])[0],$rawpost);
		$reply = Database::insert('replies',[
			'replyid'=>generate_guid(),
			'post'=>$post,
			'content'=>$content,
			'user'=>User::logged()['userid'],
			'datetime'=>time(),
			'likes'=>[],
			'floor'=>$rawpost['replies']+1,
			'repliedTo'=>$replied_to?[
				'user'=>$replied_to['user'],
				'floor'=>$replied_to['floor']
			]:null
		]);
		if($replied_to){
			$r_user = Database::select('users','userid',$replied_to['user']);
			$r_user = $r_user?$r_user[0]:[
				'userid'=>404,
				'username'=>'User Not Exists',
				'vip'=>false,
				'gender'=>'other'
			];
		}else $r_user=null;
		$markdown = new Parsedown();
		return [
			'replied'=>true,
			'message'=>'Replied!',
			'replied_to'=>$r_user,
			'reply_floor'=>$reply['floor'],
			'marked_content'=>$markdown->text($reply['content']),
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
				$post = Database::select('posts','postid',$id);
				if(!$post) return [
					'liked'=>false,
					'message'=>'Post does not exist!'
				];
				$post = $post[0];
				if(array_search($u['userid'],$post['likes'])===false){
					array_push($post['likes'],$u['userid']);
					$liked=true;
				}else foreach($post['likes'] as $k=>$v) if($v==$u['userid']) array_splice($post['likes'],$k,1);
				Database::update('posts',Database::select('posts','postid',$id),$post);
				return [
					'liked'=>true,
					'message'=>$liked?'Liked!':'Unliked!',
					'nowStatus'=>$liked
				];
			case "reply":
				$reply = Database::select('replies','replyid',$id);
				if(!$reply) return [
					'liked'=>false,
					'message'=>'Reply does not exist!'
				];
				$reply = $reply[0];
				if(array_search($u['userid'],$reply['likes'])===false){
					array_push($reply['likes'],$u['userid']);
					$liked=true;
				}else foreach($reply['likes'] as $k=>$v) if($v==$u['userid']) array_splice($reply['likes'],$k,1);
				Database::update('replies',Database::select('replies','replyid',$id),$reply);
				return [
					'liked'=>true,
					'message'=>$liked?'Liked!':'Unliked!',
					'nowStatus'=>$liked
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