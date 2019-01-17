<?php require "intialize.php"; ?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width" />
		<meta name="page-sign-verified" />
		<link rel="stylesheet" href="stylesheet.css"/>
		<title>Sign In - <?php echo Property::$properties["forum"]['name'];?></title>
		<style>
			#sign-up{
				display:none;
			}
			#sign-up:target{
				display:block;
			}
			.line{
				display:block;
				position:relative;
				bottom:0;
				right:0;
				font-size:15px;
			}
			.card-content{
				height:auto;
			}
			body{
				height:100vh;
				background:linear-gradient(to right bottom, #90FF2A,#20ADCA);
			}
		</style>
	</head>
	<body>
		<div class="central-card box-shadow">
			<div class="card-title">Sign In</div>
			<div class="card-content">
				<input id="sign-in-username" placeholder="Username" class="textfield"/>
				<div style="height:30px;"></div>
				<input id="sign-in-password" type="password" placeholder="Password" class="textfield"/>
				<div align="right" class="typo line"><a href="#sign-up">Don't have an account?</a><button id="sign-in" class="btn">Sign In</button></div>
				<div class="error" id="sign-in-error"></div>
			</div>
		</div>
		<div id="sign-up" class="central-card box-shadow">
			<div class="card-title">Sign Up</div>
			<div class="card-content">
				<input id="sign-up-username" placeholder="Username" required class="textfield"/>
				<div style="height:30px;"></div>
				<input id="sign-up-password" type="password" placeholder="Password" required class="textfield"/>
				<br>
				<div style="height:40px;line-height:40px;">
					Gender: 
					<label for="gender-male">
						<input type="radio" name="gender" id="gender-male" value="male" />
							Male
					</label>
					<label for="gender-female">
						<input type="radio" name="gender" id="gender-female" value="female" checked/>
						Female
					</label>
					<label for="gender-other">
						<input type="radio" name="gender" id="gender-other" value="other" />
						Other
					</label>
				</div>
				<div align="right" class="typo line"><a href="#">Already have an account?</a><button id="sign-up-submit" class="btn">Sign Up</button></div>
				<div class="error" id="sign-up-error"></div>
			</div>
		</div>
		<script src="javascript.js"></script>
	</body>
</html>