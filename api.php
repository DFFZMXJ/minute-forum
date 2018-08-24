<?php
/*APIs of the fourm*/
require "intialize.php";
function auto_header($state,$content_type){
	/*Set based headers automatically.*/
	$status = [
		/*Status list*/
		100 => "HTTP/1.1 100 Continue",
		101 => "HTTP/1.1 101 Switching Protocols",
		200 => "HTTP/1.1 200 OK",
		201 => "HTTP/1.1 201 Created",
		202 => "HTTP/1.1 202 Accepted",
		203 => "HTTP/1.1 203 Non-Authoritative Information",
		204 => "HTTP/1.1 204 No Content",
		205 => "HTTP/1.1 205 Reset Content",
		206 => "HTTP/1.1 206 Partial Content",
		300 => "HTTP/1.1 300 Multiple Choices",
		301 => "HTTP/1.1 301 Moved Permanently",
		302 => "HTTP/1.1 302 Found",
		303 => "HTTP/1.1 303 See Other",
		304 => "HTTP/1.1 304 Not Modified",
		305 => "HTTP/1.1 305 Use Proxy",
		307 => "HTTP/1.1 307 Temporary Redirect",
		400 => "HTTP/1.1 400 Bad Request",
		401 => "HTTP/1.1 401 Unauthorized",
		402 => "HTTP/1.1 402 Payment Required",
		403 => "HTTP/1.1 403 Forbidden",
		404 => "HTTP/1.1 404 Not Found",
		405 => "HTTP/1.1 405 Method Not Allowed",
		406 => "HTTP/1.1 406 Not Acceptable",
		407 => "HTTP/1.1 407 Proxy Authentication Required",
		408 => "HTTP/1.1 408 Request Time-out",
		409 => "HTTP/1.1 409 Conflict",
		410 => "HTTP/1.1 410 Gone",
		411 => "HTTP/1.1 411 Length Required",
		412 => "HTTP/1.1 412 Precondition Failed",
		413 => "HTTP/1.1 413 Request Entity Too Large",
		414 => "HTTP/1.1 414 Request-URI Too Large",
		415 => "HTTP/1.1 415 Unsupported Media Type",
		416 => "HTTP/1.1 416 Requested range not satisfiable",
		417 => "HTTP/1.1 417 Expectation Failed",
		500 => "HTTP/1.1 500 Internal Server Error",
		501 => "HTTP/1.1 501 Not Implemented",
		502 => "HTTP/1.1 502 Bad Gateway",
		503 => "HTTP/1.1 503 Service Unavailable",
		504 => "HTTP/1.1 504 Gateway Time-out"
	];
	/*Set the headers.*/
	header($status[$state]);
	header("Content-type:".$content_type);
}
$_POST = json_decode(file_get_contents("php://input"),true);
if(empty($_GET['thing'])){
	/*Default return*/
	auto_header(406,"application/json");
	die(json_encode([
		'status'=>406,
		'message'=>'You must enter enough information!'
	],JSON_PRETTY_PRINT));
}else switch(strtolower($_GET['thing'])){
	case 'signin':
		/*Log in*/
		if(empty($_POST['username'])||empty($_POST['password'])){
			auto_header(406,"application/json");
			die(json_encode([
				'status'=>406,
				'message'=>'Username and password cannot be empty!'
			],JSON_PRETTY_PRINT));
		}else{
			if(($logged = User::login($_POST['username'],$_POST['password'],true))['logged']){
				auto_header(200,"application/json");
				die(json_encode([
					'status'=>200,
					'message'=>$logged['message']
				],JSON_PRETTY_PRINT));
			}else{
				auto_header(401,"application/json");
				die(json_encode([
					'status'=>401,
					'message'=>$logged['message']
				],JSON_PRETTY_PRINT));
			}
		}
		break;
	case 'signup':
		/*Register a new account*/
		if(User::logged()){
			auto_header(403,"application/json");
			die(json_encode([
				'status'=>403,
				'message'=>'You are already signed in!'
			],JSON_PRETTY_PRINT));
		}
		if(empty($_POST['username'])||empty($_POST['password'])||empty($_POST['gender'])){
			auto_header(406,"application/json");
			die(json_encode([
				'status'=>406,
				'message'=>'Username, password and gender cannot be empty!'
			],JSON_PRETTY_PRINT));
		}else{
			if(($registered = User::register($_POST['username'],$_POST['password'],$_POST['gender']))['registered']){
				User::login($_POST['username'],$_POST['password'],true);
				auto_header(201,"application/json");
				die(json_encode([
					'status'=>201,
					'message'=>$registered['message']
				],JSON_PRETTY_PRINT));
			}else{
				auto_header(406,"application/json");
				die(json_encode([
					'status'=>406,
					'message'=>$registered['message']
				],JSON_PRETTY_PRINT));
			}
		}
		break;
	case 'signout':
		if(!User::logged()){
			auto_header(401,"application/json");
			die(json_encode([
				'status'=>401,
				'message'=>'You are not signed in!'
			],JSON_PRETTY_PRINT));
		}else{
			auto_header(200,"application/json");
			die(json_encode([
				'status'=>200,
				'message'=>User::logout()['message']
			],JSON_PRETTY_PRINT));
		}
		break;
	case 'post':
		if(!User::logged()){
			auto_header(401,"application/json");
			die(json_encode([
				'status'=>401,
				'message'=>'You are not signed in!'
			],JSON_PRETTY_PRINT));
		}else if(empty($_POST['title'])||empty($_POST['content'])){
			auto_header(406,"application/json");
			die(json_encode([
				'status'=>406,
				'message'=>'Title and content cannot be empty!'
			],JSON_PRETTY_PRINT));
		}else{
			if(($created = Post::create($_POST['title'],$_POST['content']))['created']){
				auto_header(201,"application/json");
				die(json_encode([
					'status'=>201,
					'message'=>$created['message'],
					'post'=>$created['id']
				],JSON_PRETTY_PRINT));
			}else{
				auto_header(406,"application/json");
				die(json_encode([
					'status'=>406,
					'message'=>$created['message']
				],JSON_PRETTY_PRINT));
			}
		}
		break;
	case 'reply':
		if(!User::logged()){
			auto_header(401,"application/json");
			die(json_encode([
				'status'=>401,
				'message'=>'You are not signed in!'
			],JSON_PRETTY_PRINT));
		}else if(empty($_POST['content'])||empty($_POST['post'])){
			auto_header(406,"application/json");
			die(json_encode([
				'status'=>406,
				'message'=>'Content, post-id and replied-to options cannot be empty!'
			],JSON_PRETTY_PRINT));
		}else if(($replied = Post::reply($_POST["post"],$_POST["content"],$_POST["repliedto"]?$_POST['repliedto']:null))['replied']){
			auto_header(201,"application/json");
			die(json_encode([
				'status'=>201,
				'message'=>'Replied!',
				'repliedTo'=>$replied['replied_to'],
				'floor'=>$replied['reply_floor'],
				'content'=>$replied['marked_content'],
				'id'=>$_POST['post']
			],JSON_PRETTY_PRINT));
		}else{
			auto_header(406,"application/json");
			die(json_encode([
				'status'=>406,
				'message'=>$replied['message']
			],JSON_PRETTY_PRINT));
		}
		break;
	case 'like':
		/*Like/Unlike a post/reply.*/
		if(!User::logged()){
			auto_header(401,"application/json");
			die(json_encode([
				'status'=>401,
				'message'=>'You are not signed in!'
			],JSON_PRETTY_PRINT));
		}else if(empty($_POST['type'])||empty($_POST['id'])){
			auto_header(406,"application/json");
			die(json_encode([
				'status'=>406,
				'message'=>'ID and like-type options cannot be empty!'
			],JSON_PRETTY_PRINT));
		}else if(($liked = Post::like($_POST['type'],$_POST['id']))){
			auto_header(200,"application/json");
			die(json_encode([
				'status'=>200,
				'message'=>$liked['message'],
				'liked'=>$liked['nowStatus'],
				'fullStr'=>isset($_POST['fullStr'])?$_POST['fullStr']:'{}'
			],JSON_PRETTY_PRINT));
		}else{
			auto_header(406,"application/json");
			die(json_encode([
				'status'=>406,
				'message'=>$liked['message']
			],JSON_PRETTY_PRINT));
		}
		break;
	case 'info-modify':
		/*Coming in future: user information edit.*/
		break;
	default:
		/*If user did unsupported action.*/
		auto_header(406,"application/json");
		die(json_encode([
			'status'=>406,
			'message'=>'Your operation is unsupported!'
		],JSON_PRETTY_PRINT));
		break;
}