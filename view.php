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
		<title>Not Found - <?php echo Property::$properties["forum"]['name'];?></title>
	</head>
	<body>
		<div class="central-card box-shadow">
			<div class="card-title">Very Sorry</div>
			<div class="card-content">
				<p>
					This post may be unavailable because of these reasons below:
				</p>
				<ul>
					<li>Link has expired.</li>
					<li>Contents are not peaceful.</li>
					<li>Deleted by author.</li>
					<li>Violated others' copyrights.</li>
				</ul>
				<footer>&copy;2018 <?php echo Property::$properties['forum']['name'];?>. All rights are reversed.</footer>
			</div>
		</div>
	</body>
</html>
		<?php
		exit();
	}
	$post = Database::select('posts','postid',$_GET["post"])[0];
	Database::execute((new SQL)->update('posts')->set('views',++$post['views'])->where('postid=',$_GET['post'])->getSql());
	$user = Database::query((new SQL)->select('*')->from('users')->where('userid=',$post['user'])->getSql(),[
		'userid'=>$post['user'],
		'username'=>'{DELETED}',
		'vip'=>'false',
		'gender'=>'other',
		'token'=>'{Unexisting-User}'
	]);
	$replies = Database::select('replies','post',$post['postid']);
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" href="stylesheet.css"/>
		<title><?php echo $post['title'];?> - <?php echo Property::$properties["forum"]['name'];?></title>
	</head>
	<body>
		<?php require "nav.php";?>
		<div class="container ui-row">
			<div class="ui-content transparent" style="background:transparent;">
				<div class="post box-shadow">
					<div class="post-header">
						<h1 class="post-title"><?php echo $post['title'];?></h1>
						<div class="post-subheader"><a class="<?php if($user['vip']) echo "user-vip"; ?>" href="profile.php?user=<?php echo $post['user'];?>"><?php echo $user['username'];?></a> · <?php echo date('Y/m/d H:i',$post['datetime']);?> · <?php echo $post['views']." view".($post['views']!=1?'s':'');?></div>
					</div>
					<div class="post-content typo">
						<?php $markdown = new HyperDown\Parser(); echo $markdown->makeHtml($post['content']); ?>
					</div>
					<div class="post-control">
						<?php 
							$like_info = json_decode($post['likes'],true);
						?>
						<a class="post-like" data-like='{"type":"post","id":"<?php echo $post['postid']; ?>","liked":<?php echo array_search((User::logged())?User::logged()['userid']:'impossible',$like_info)!==false?'true':'false'; ?>}' href="javascript:void(0);"><?php echo array_search((User::logged())?User::logged()['userid']:'impossible',$like_info)!==false?'Unlike':'Like'; ?></a> · <a href="javascript:reply('post',[`<?php echo $post['postid'];?>`]);">Reply</a>
					</div>
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
								$replied_to = json_decode($replies[$i]['repliedTo'],true);
								$mentioned = Database::query(
									(new SQL)->select('*')->from('users')->where('userid=',$replied_to['user'])->getSql(),
									[
										'userid'=>404,
										'username'=>'{Unknown User}',
										'vip'=>false,
										'gender'=>'other'
									]
								);
							?><a <?php if($mentioned['vip']) echo "class=\"user-vip\"";?> href="#reply-<?php echo $replied_to['floor']; ?>">@<?php echo $mentioned['username']; ?>#<?php echo $replied_to['floor']; ?></a><?php }?>
							<?php echo $markdown->makeHtml($replies[$i]['content']);?>
						</div>
						<div class="reply-control">
							<?php $reply_like_info = json_decode($replies[$i]['likes'],true);//Prevent unexpected errors. ?>
							<a class="post-like" data-like='{"type":"reply","id":"<?php echo $replies[$i]['replyid']; ?>","liked":<?php echo array_search((User::logged())!==false?User::logged()['userid']:'impossible',$reply_like_info)!==false?'true':'false'; ?>}' href="javascript:void(0);"><?php echo array_search((User::logged())?User::logged()['userid']:'impossible',$reply_like_info)?'Unlike':'Like'; ?></a> · <a href="javascript:reply('reply',[`<?php echo $post['postid']; ?>`,`<?php echo $replies[$i]['floor'];?>`,`<?php echo $replier['userid'];?>`]);">Reply</a>
						</div>
					</div>
				<?php }}?></div>
			</div>
			<div class="ui-right box-shadow">
				<h3>ANNOUNCEMENT</h3>
				<div><?php echo Property::$properties['forum']['announcement'];?></div>
			</div>
		</div>
		<div class="reply-box"></div>
		<script src="javascript.js"></script>
	</body>
</html>