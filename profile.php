<?php 
	require "intialize.php"; 
	if(isset($_GET['user'])){
		$user = Database::select('users','userid',$_GET['user']);
		if(!$user){
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
		}else $user = $user[0];
	}else{
		$user = User::logged();
	}
	$posts = Database::select('posts','user',$user['userid']);
	$replies = Database::select('replies','user',$user['userid']);
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" href="stylesheet.css"/>
		<title><?php echo $user['username'];?> - <?php echo $config["sitename"];?></title>
	</head>
	<body>
		<?php require "nav.php"; ?>
		<div class="container ui-row box-shadow user-info">
			<div class="user-basic-info">
				<div class="username__primary-info">
					<div class="primary-info__username <?php if($user['vip']) echo "user-vip"; ?>"><?php echo $user['username']; ?></div>
					<div class="primary-info__details"><?php switch($user['gender']){
						case 'male':
							echo "Male";
							break;
						case 'female':
							echo 'Female';
							break;
						case 'other':
							echo 'Other';
							break;
						default:
							echo 'Error loading on gender';
							break;
					}?>, <?php echo ((string)count($posts))." post".(count($posts)>1?'s':'');?>, <?php echo ((string)count($replies))." repl".(count($replies)>1?'ies':'y'); ?>, joined at <?php echo date('H:i',$user['registered']); ?> on <?php echo date('M d',$user['registered']); ?> in <?php echo date('Y',$user['registered']); ?>.</div>
				</div>
			</div>
			<div class="subheader"><?php echo $user['gender']=='female'?'Her':'His';?> posts</div>
			<ul class="post-list"><?php for($i=0;$i < count($posts);$i++){?><li data-post="<?php echo $posts[$i]['postid'];?>">
				<div class="info">
					<div class="title"><?php echo $posts[$i]['title'];?></div>
					<div class="info-text">Posted by <?php echo $user['username']; ?>, <?php echo $posts[$i]['views'];?> views.</div>
				</div>
				<div class="sideheader"><?php echo date('Y/m/d H:i',$posts[$i]['datetime']);?></div>
			</li><?php }?></div>
		</div>
		<script src="javascript.js"></script>
	</body>
</html>