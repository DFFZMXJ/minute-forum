<?php require "intialize.php"; ?>
<?php 
	if(empty($_GET["post"])||!Database::select('posts','postid',$_GET["post"])){
		header("HTTP/1.1 404 Not Found");
		?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" href="stylesheet.css"/>
		<title>Not Found - <?php echo $config["sitename"];?></title>
	</head>
	<body>
		<div class="central-card box-shadow">
			<div class="card-title">404 Not Found</div>
			<div class="card-content">
				The resource of you request is not found! Check your URL and try again!
				<br>
				URL: <?php echo (empty($_SERVER["HTTPS"])?"http:":"https:")."//".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"];?>
				<br>
				Request Method:<?php echo $_SERVER["REQUEST_METHOD"];?>
			</div>
		</div>
	</body>
</html>
		<?php
		die();
	}
	$post = Database::select('posts','postid',$_GET["post"])[0];
	$post['views']++;
	Database::update('posts',Database::select('posts','postid',$_GET["post"])[0],$post);
	$user = Database::select('users','userid',$post['user']);
	if(!$user) $user = [
		'userid'=>'404',
		'username'=>'{Unknown User}',
		'vip'=>false,
		'gender'=>'other',
		'token'=>'{Invalid token}'
	];
	else $user=$user[0];
	$replies = Database::select('replies','post',$post['postid']);
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" href="stylesheet.css"/>
		<title><?php echo $post['title'];?> - <?php echo $config["sitename"];?></title>
	</head>
	<body>
		<?php require "nav.php";?>
		<div class="container ui-row">
			<div class="ui-content transparent" style="background:transparent;">
				<div class="post box-shadow">
					<div class="post-header">
						<h1 class="post-title"><?php echo $post['title'];?></h1>
						<div class="post-subheader"><a class="<?php if($user['vip']) echo "user-vip"; ?>" href="profile.php?user=<?php echo $post['user'];?>"><?php echo $user['username'];?></a> · <?php echo date('Y/m/d H:i',$post['datetime']);?> · <?php echo $post['views']." View".($post>1?'s':'');?></div>
					</div>
					<div class="post-content typo">
						<?php $markdown = new Parsedown(); echo $markdown->text($post['content']); ?>
					</div>
					<div class="post-control"><a class="post-like" data-like='{"type":"post","id":"<?php echo $post['postid']; ?>","liked":<?php echo array_search((User::logged())?User::logged()['userid']:'impossible',$post['likes'])!==false?'true':'false'; ?>}' href="javascript:void(0);"><?php echo array_search((User::logged())?User::logged()['userid']:'impossible',$post['likes'])!==false?'Unlike':'Like'; ?></a> · <a href="javascript:reply('post',[`<?php echo $post['postid'];?>`]);">Reply</a></div>
				</div>
				<div class="replies" id="reply-list"><?php if($replies){
				for($i = 0; $i < count($replies); $i++){
					$replier = Database::select('users','userid',$replies[$i]['user']);
					$replier = $replier?$replier[0]:[
		'userid'=>'404',
		'username'=>'{Unknown User}',
		'vip'=>false,
		'gender'=>'other'
	];?>
					<div class="post reply box-shadow" id="reply-<?php echo $replies[$i]['floor']; ?>">
						<div class="reply-header">
							<a class="reply-username <?php if($replier['vip']) echo "user-vip"; ?>" href="profile.php?user=<?php echo $replier['userid']; ?>"><?php echo $replier['username'];?></a>
							<div class="reply-addition"><?php echo date('Y/m/d H:i',$replies[$i]['datetime']);?></div>
							<div class="reply-floor">Reply #<?php echo $replies[$i]['floor'];?></div>
						</div>
						<div class="reply-content typo">
							<?php if($replies[$i]['repliedTo']){
								$mentioned = Database::select('users','userid',$replies[$i]['repliedTo']['user']);
								$mentioned = $mentioned?$mentioned[0]:[
									'userid'=>'404',
									'username'=>'{Unknwon User}',
									'vip'=>false,
									'gender'=>'other'
								];
							?><a <?php if($mentioned['vip']) echo "class=\"user-vip\"";?> href="#reply-<?php echo $replies[$i]['repliedTo']['floor']; ?>">@<?php echo $mentioned['username']; ?>#<?php echo $replies[$i]['repliedTo']['floor']; ?></a><?php }?>
							<?php echo $markdown->text($replies[$i]['content']);?>
						</div>
						<div class="reply-control">
							<a class="post-like" data-like='{"type":"reply","id":"<?php echo $replies[$i]['replyid']; ?>","liked":<?php echo array_search((User::logged())!==false?User::logged()['userid']:'impossible',$replies[$i]['likes'])!==false?'true':'false'; ?>}' href="javascript:void(0);"><?php echo array_search((User::logged())?User::logged()['userid']:'impossible',$replies[$i]['likes'])?'Unlike':'Like'; ?></a> · <a href="javascript:reply('reply',[`<?php echo $post['postid']; ?>`,`<?php echo $replies[$i]['floor'];?>`,`<?php echo $replier['userid'];?>`]);">Reply</a>
						</div>
					</div>
				<?php }}?></div>
			</div>
			<div class="ui-right box-shadow">
				<h3>ANNOUNCEMENT</h3>
				<div><?php echo $config['banner'];?></div>
			</div>
		</div>
		<div class="reply-box"></div>
		<script src="javascript.js"></script>
	</body>
</html>