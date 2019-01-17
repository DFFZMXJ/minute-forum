<?php
/*Opened APIs of the fourm.*/
require "intialize.php";
function api_puts($status=0,$message,$data=null,$continue_processing=false){
	/*Return formated JSON automatically.*/
	$status_list = [
		/*Status list*/
		0 => "HTTP/1.1 0 Undefined",
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
	header($status_list[$status]);
	header("Content-type: application/json");
	echo json_encode([
		'status'=>$status,
		'message'=>$message,
		'data'=>$data
	],JSON_PRETTY_PRINT);//Optimize JSON appearence
	if(!$continue_processing) exit();//exit==die
	return true;
}
$_POST = json_decode(file_get_contents("php://input"),true);
if(empty($_GET['thing'])){
	/**
	 * I wanted to change the attribute 'thing' to 'operation',
	 * but I'm lazy to change JavaScript file, sorry.
	 */
	api_puts(406,'Operation is undefined!');
}else switch(strtolower($_GET['thing'])){
	case 'signin':
		/*Log in*/
		if(empty($_POST['username'])||empty($_POST['password']))
			api_puts(406,'Username or password are empty!');
		else if(($logged = User::login($_POST['username'],$_POST['password'],true))['logged'])
			api_puts(200,$logged['message']);
		else
			api_puts(401,$logged['message']);
		break;
	case 'signup':
		/*Register a new account*/
		if(User::logged()) api_puts(403,'You\'ve already signed in!');
		if(empty($_POST['username'])||empty($_POST['password'])||empty($_POST['gender']))
			api_puts(406,'Username, password or gender are empty!');
		else if(($registered = User::register($_POST['username'],$_POST['password'],$_POST['gender']))['registered']){
				User::login($_POST['username'],$_POST['password'],true);
				api_puts(201,$registered['message']);
			}else
				api_puts(406,$registered['message']);
		break;
	case 'signout':
		if(!User::logged())
			api_puts(401,'You are not signed in!');
		else
			api_puts(200,User::logout()['message']);
		break;
	case 'post':
		if(!User::logged())
			api_puts(401,'You\'re not signed in!');
		else if(empty($_POST['title'])||empty($_POST['content']))
			api_puts(401,'Title or content are empty!');
		else if(($created = Post::create($_POST['title'],$_POST['content']))['created'])
			api_puts(201,$created['message'],[
				'post'=>$created['id']
			]);
		else
			api_puts(406,$created['message']);
		break;
	case 'reply':
		if(!User::logged())
			api_puts(401,"You're not signed in!");
		else if(empty($_POST['content'])||empty($_POST['post']))
			api_puts(406,"Contents and Post ID are requried!");
		else if(($replied = Post::reply($_POST["post"],$_POST["content"],$_POST["repliedto"]?$_POST['repliedto']:null))['replied'])
			api_puts(201,'Replied!',[
				'repliedTo'=>$replied['replied_to'],
				'floor'=>$replied['reply_floor'],
				'content'=>$replied['marked_content'],
				'id'=>$_POST['post']
			]);
		else
			api_puts(406,$replied['message']);
		break;
	case 'like':
		/*Like/Unlike a post/reply.*/
		if(!User::logged())
			api_puts(401,'You are not signed in!');
		else if(empty($_POST['type'])||empty($_POST['id']))
			api_puts(406,'Type and ID are required!');
		else if(($liked = Post::like($_POST['type'],$_POST['id'])))
			api_puts(200,$liked['message'],[
				'liked'=>$liked['currentStatus'],
				'fullString'=>isset($_POST['fullStr'])?$_POST['fullStr']:'{}'
			]);
		else
			api_puts(406,$liked['message']);
		break;
	case 'info-modify':
		/*Coming in future: user information edit.*/
		api_puts(503,"Operation is not supported!");
		break;
	default:
		/*If user did unsupported action.*/
		api_puts(404,"Operation not found!");
		break;
}