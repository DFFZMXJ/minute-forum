<?php require "intialize.php"; ?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" href="stylesheet.css"/>
		<title><?php echo $config["sitename"];?></title>
	</head>
	<body>
		<?php require "nav.php";?>
		<div class="container ui-row">
			<div class="ui-content box-shadow">
				<?php 
					$stickies = Database::select('posts','sticky',true);
					if($stickies){
				?>
				<div class="subheader">Sticky</div>
				<ul class="post-list">
				<?php for($i = 0; $i < count($stickies); $i++){?>
					<li data-post="<?php echo $stickies[$i]['postid'];?>">
						
						<div class="info">
							<div class="title"><?php echo $stickies[$i]['title'];?></div>
							<div class="info-text">Posted by <?php echo Database::select('users','userid',$stickies[$i]['user'])[0]['username']; ?>, <?php echo $stickies[$i]['views'];?> views.</div>
						</div>
						<div class="sideheader"><?php echo date('Y/m/d H:i',$stickies[$i]['datetime']);?></div>
					</li>
				<?php }?>
				</ul>
				<?php }?>
				<div class="subheader">All Posts</div>
				<?php 
					$posts = Database::select('posts','sticky',false);
				?>
				<ul class="post-list"><?php for($i=0;$i<($posts?count($posts):0);$i++){?><li data-post="<?php echo $posts[$i]['postid'];?>">
						
						<div class="info">
							<div class="title"><?php echo $posts[$i]['title'];?></div>
							<div class="info-text">Posted by <?php $publisher = Database::select('users','userid',$posts[$i]['user']); echo $publisher?$publisher[0]['username']:'{Unknown User}'; ?>, <?php echo $posts[$i]['views'];?> views.</div>
						</div>
						<div class="sideheader"><?php echo date('Y/m/d H:i',$posts[$i]['datetime']);?></div>
					</li><?php }?></ul>
			</div>
			<div class="ui-right box-shadow">
				<h3>ANNOUNCEMENT</h3>
				<div><?php echo $config['banner'];?></div>
			</div>
		</div>
		<script src="javascript.js"></script>
	</body>
</html>