<?php 
if(file_exists("properties.php"))
	require "intialize.php";
else
	die("5 Minute Forum hasn't setup yet! Please setup through setup.php."); ?>
<!--
	手持两把锟斤拷，口中疾呼烫烫烫。
	脚踏千朵屯屯屯，笑看万物锘锘锘。
-->
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" href="stylesheet.css"/>
		<title><?php echo Property::$properties["forum"]['name'];?></title>
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
					//Here's to be improved but I don't want to improve here now.
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
				<div><?php echo Property::$properties['forum']['announcement'];?></div>
			</div>
		</div>
		<script src="javascript.js"></script>
	</body>
</html>