<header class="nav">
	<nav class="nav-container">
		<a href="./" non-link-style class="brand"><?php echo $config["sitename"];?></a>
		<div class="user">
			<?php if(User::logged()){?><a class="username <?php if(User::logged()['vip']) echo "user-vip";?>" title="View Profile" href="profile.php"><?php echo User::logged()['username'];?></a> | <a href="javascript:signOut();">Sign Out</a><?php }else{?><a class="username" href="auth.php">Sign In</a><?php }?>
		</div>
	</nav>
	<pre id="hidden-user-card-validate"><?php echo json_encode(User::logged());?></pre>
</header>
<div class="bottom-dialog" id="post-dialog">
	<div class="dialog-primary">
		<input class="textfield" type="text" id="post-title" placeholder="Title..." />
		<button class="btn-icon" id="dialog-close">+</button>
	</div>
	<div class="dialog-content">
		<textarea class="beauty-textarea" id="post-content" required placeholder="Content"></textarea>
		<div class="error" id="post-error"></div>
	</div>
	<div><button id="submit-post" class="btn">submit</button></div>
</div>
<button class="fab new-post" title="New Post">+</button>