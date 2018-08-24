/*Front-end JavaScript.*/
~(function () {
	window.addEventListener("load", function () {
		var p = document.querySelectorAll('.post-list li[data-post]');
		for (let i = 0; i < p.length; i++) p[i].addEventListener('click', function () {
			window.location.href = `view.php?post=${this.getAttribute('data-post')}`;
		});
	});
	window.AJAX = function (options = {}) {
		/*AJAX Sender*/
		var method, url, async, complete, change, headers, data;
		method = (options.method || 'GET').toUpperCase();
		url = options.url || '#';
		async = (typeof options.async == "boolean") ? options.async : true;
		complete = (typeof options.complete == "function") ? options.complete : function () { };
		change = (typeof options.change == "function") ? options.change : function () { };
		headers = options.headers || {};
		data = options.data;
		var xhr = window.XMLHttpRequest ? new window.XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
		xhr.open(method, url, async);
		if (typeof data == "object") {
			xhr.setRequestHeader("Content-Type", "application/json; Charset=UTF-8");
		}
		for (let header in headers) xhr.setRequestHeader(header, headers[header]);
		xhr.complete = complete;
		xhr.change = change;
		xhr.onreadystatechange = function () {
			if (xhr.readyState == 4) { this.complete(this); console.info("AJAX Sent!", this); }
			this.change(this);
		}
		xhr.send(data ? (typeof data == "object" ? JSON.stringify(data) : data) : undefined);
		return xhr;
	}
	window.signOut = function () {
		/*Sign out from an account*/
		AJAX({
			method: 'GET',
			url: 'api.php?thing=signout',
			complete: xhr => window.location.reload()
		});
	}
	if (document.querySelector("meta[name=\"page-sign-verified\"]")) {
		/*If it's sign in page, load sign in plugins.*/
		document.getElementById("sign-in").addEventListener("click", function () {
			var username = document.querySelector("#sign-in-username").value,
				password = document.querySelector("#sign-in-password").value;
			if (username == '' || password == '') return document.querySelector("#sign-in-error").innerHTML = "Username and password are required";
			AJAX({
				method: 'POST',
				url: 'api.php?thing=signin',
				data: {
					username: username,
					password: password
				},
				complete: xhr => {
					if (xhr.status != 200) document.querySelector("#sign-in-error").innerHTML = JSON.parse(xhr.responseText).message;
					else {
						document.querySelector("#sign-in-error").innerHTML = "";
						window.location.href = "./";
					}
				}
			});
		});
		document.getElementById("sign-up-submit").addEventListener("click", function () {
			var username = document.querySelector("#sign-up-username").value,
				password = document.querySelector("#sign-up-password").value;
			var gender = null;
			for (var i = 0; i < document.querySelectorAll("[name=\"gender\"]").length; i++) {
				if (document.querySelectorAll("[name=\"gender\"]")[i].checked) gender = document.querySelectorAll("[name=\"gender\"]")[i].value;
			};
			if (username == "" || password == "") return document.querySelector("#sign-up-error").innerHTML = "Username and password are required!";
			AJAX({
				url: 'api.php?thing=signup',
				method: 'POST',
				data: {
					username: username,
					password: password,
					gender: gender
				},
				complete: xhr => {
					if (xhr.status < 300 && xhr.status > 199) {
						document.getElementById("sign-up-error").innerHTML = "";
						window.location.href = "./";
					} else document.getElementById("sign-up-error").innerHTML = JSON.parse(xhr.responseText).message;
				}
			});
		});
	}
	window["\\Post-Editor"] = {
		repliedTo: null,
		titleLocked: false,
		title: document.querySelector("#post-title"),
		content: document.querySelector("#post-content"),
		reply: false,
		dialog: document.querySelector("#post-dialog"),
		error: document.querySelector("#post-error")
	};
	window["\\UserLoginState"] = JSON.parse(document.querySelector("#hidden-user-card-validate").innerHTML);//Hidden global variable to check the validate of user sign in.
	document.querySelector("#hidden-user-card-validate").remove();//Remove element to hide for non-developers.
	document.querySelector(".new-post").addEventListener("click", function () {
		if (window["\\Post-Editor"].content.value == "" || confirm("Discard your content?")) {
			window["\\Post-Editor"].content.value = "";
			window["\\Post-Editor"].title.value = "";
			window["\\Post-Editor"].title.removeAttribute("disabled");
			var classes = window["\\Post-Editor"].dialog.getAttribute("class").trim().split(" ");
			if (classes.indexOf("expand") == -1) classes.push("expand");
			if (classes.indexOf("expand-half") == -1) classes.push("expand-half");
			window["\\Post-Editor"].dialog.setAttribute("class", classes.join(" "));
		}
	});
	document.querySelector("#dialog-close").addEventListener("click", function () {
		var classes = window["\\Post-Editor"].dialog.getAttribute("class").trim().split(" ");
		if (classes.indexOf("expand") >= 0) { classes.splice(classes.indexOf("expand"), 1); }
		else { classes.push("expand"); }
		window["\\Post-Editor"].dialog.setAttribute("class", classes.join(" "));
	});
	document.querySelector("#submit-post").addEventListener("click", function () {
		if (window["\\Post-Editor"].title.value == "" || window["\\Post-Editor"].content.value == "") return window["\\Post-Editor"].error.innerHTML = "Title and content are required!";
		window["\\Post-Editor"].error.innerHTML = "";
		if (window["\\Post-Editor"].reply) AJAX({
			method: 'POST',
			url: 'api.php?thing=reply',
			data: {
				content: window["\\Post-Editor"].content.value,
				post: window["\\Post-Editor"].reply,
				repliedto: window["\\Post-Editor"].repliedTo
			},
			complete: xhr => {
				var data = JSON.parse(xhr.responseText);
				if (xhr.status == 201) {
					/*Create a reply box*/
					var replyBox = document.createElement("div");
					replyBox.setAttribute("class", "post reply box-shadow");
					replyBox.setAttribute("id", "reply-" + data.floor);
					var replyBoxHeader = document.createElement("div");
					replyBoxHeader.setAttribute("class", "reply-header");
					var replyBoxHeaderUsername = document.createElement("div");
					replyBoxHeaderUsername.setAttribute("class", "reply-username" + (window["\\UserLoginState"].vip ? ' user-vip' : ''));
					replyBoxHeaderUsername.setAttribute("href",`profile.php?user=${window["\\UserLoginState"].userid}`);
					replyBoxHeaderUsername.innerHTML = window["\\UserLoginState"].username;
					var replyBoxHeaderAddition = document.createElement("div");
					replyBoxHeaderAddition.setAttribute("class", "reply-addition");
					replyBoxHeaderAddition.innerText = (function () {
						let d = new Date();
						return d.getFullYear() + "/" + (d.getMonth() + 1) + "/" + d.getDate() + " " + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
					})();
					var replyBoxHeaderFloor = document.createElement("div");
					replyBoxHeaderFloor.setAttribute("class", "reply-floor");
					replyBoxHeaderFloor.innerHTML = "Reply #" + data.floor;
					var replyBoxContent = document.createElement("div");
					replyBoxContent.setAttribute("class", "reply-content typo");
					replyBoxContent.innerHTML = data.content;
					var replyBoxContentMentioned = document.createElement("a");
					if (data.repliedTo) {
						if (data.repliedTo.vip) replyBoxContentMentioned.setAttribute("class", "user-vip");
						replyBoxContentMentioned.setAttribute("href", "#reply-" + window["\\Post-Editor"].repliedTo.floor);
						replyBoxContentMentioned.innerHTML = "@" + data.repliedTo.username + "#" + window["\\Post-Editor"].repliedTo.floor;
					}
					var replyBoxControl = document.createElement("div");
					replyBoxControl.setAttribute("class", "reply-control");
					var replyBoxControlLike = document.createElement("a");
					replyBoxControlLike.setAttribute("href","javascript:void(0);");
					replyBoxControlLike.setAttribute("class", "post-like");
					replyBoxControlLike.setAttribute("data-like", JSON.stringify({
						type: 'reply',
						id: data.id,
						liked: false
					}));
					replyBoxControlLike.addEventListener("click", window._like);
					replyBoxControlLike.innerHTML = "Like";
					var replyBoxControlReply = document.createElement("a");
					replyBoxControlReply.setAttribute("href", "javascript:reply('reply'," + JSON.stringify([
						data.id,
						data.floor,
						window['\\UserLoginState'].userid
					]) + ")");
					replyBoxControlReply.innerHTML = "Reply";
					/*Intengreate the elements*/
					replyBoxHeader.appendChild(replyBoxHeaderUsername);
					replyBoxHeader.appendChild(replyBoxHeaderAddition);
					replyBoxHeader.appendChild(replyBoxHeaderFloor);
					replyBox.appendChild(replyBoxHeader);
					if (data.repliedTo) replyBoxContent.insertBefore(replyBoxContentMentioned,replyBoxContent.childNodes[0]);
					replyBox.appendChild(replyBoxContent);
					replyBoxControl.innerHTML=" · ";
					replyBoxControl.insertBefore(replyBoxControlLike,replyBoxControl.childNodes[0]);
					replyBoxControl.appendChild(replyBoxControlReply);
					replyBox.appendChild(replyBoxControl);
					/*Append into reply pool*/
					document.getElementById("reply-list").appendChild(replyBox);
					/*Reset reply information*/
					window["\\Post-Editor"].title.value = "";
					window["\\Post-Editor"].title.removeAttribute("disabled");
					window["\\Post-Editor"].repliedTo = null;
					window["\\Post-Editor"].reply = false;
					window["\\Post-Editor"].content.value = "";
					window["\\Post-Editor"].dialog.setAttribute("class", "bottom-dialog");
				} else {
					window["\\Post-Editor"].error.innerHTML = data.message;
				}
			}
		});
		else AJAX({
			method: 'POST',
			url: 'api.php?thing=post',
			data: {
				title: window["\\Post-Editor"].title.value,
				content: window["\\Post-Editor"].content.value
			},
			complete: xhr => {
				var r = JSON.parse(xhr.responseText);
				switch (xhr.status) {
					case 201:
					case 200:
						if (r.post) window.location.href = `view.php?post=${r.post}`;
						break;
					case 401:
						window.location.href = "auth.php";
						break;
					case 406:
						window["\\Post-Editor"].error = r.message;
						break;
					default:
						window["\\Post-Editor"].error = `Unknown error, message: ${r.message}`;
				}
			}
		});
	});
	window.reply = function (type, info) {
		/**
		 * Reply a post/a comment.
		 * This function is to initalize reply and toggle reply dialog.
		 */

		if (window["\\Post-Editor"].content.value.trim() == "" || confirm("Discard your content?")) {
			window["\\Post-Editor"].reply = info[0];
			window["\\Post-Editor"].titleLocked = true;
			window["\\Post-Editor"].title.value = "Reply to " + (type.toLowerCase() == "reply" ? "#" + info[1] : "post") + ".";
			window["\\Post-Editor"].title.setAttribute("disabled","disabled");
			window["\\Post-Editor"].repliedTo = type.toLowerCase() == "reply" ? {
				user: info[2],
				floor: info[1]
			} : null;
			window["\\Post-Editor"].content.value = "";
			window["\\Post-Editor"].error.innerHTML = "";
			window["\\Post-Editor"].dialog.setAttribute("class","bottom-dialog expand-half expand");
		}
	}
	window._like = function () {
		var info = this.getAttribute("data-like");
		var _info = JSON.parse(info);
		if (_info.liked)
			this.innerHTML = "Like";
		else
			this.innerHTML = "Unlike";
		AJAX({
			url: "api.php?thing=like",
			method: "POST",
			complete: xhr => {
				/*When received*/
				if (xhr.status == 200) {
					let data = JSON.parse(xhr.responseText);
					let button = document.querySelector(`[data-like="${data.fullStr.replace(/\"/g, "\\\"")}"]`);
					button.innerHTML = data.liked ? 'Unlike' : 'Like';
					button.setAttribute("data-like", data.fullStr);
				} else {
					alert(`Network error! Please try again later!
Status: ${xhr.status}
Response: ${xhr.responseText}`);
					location.history.reload();
				}
			},
			data: {
				type: _info.type,
				id: _info.id,
				fullStr: info
			}
		});
	}
	!(function () {
		let likes = document.querySelectorAll(".post-like");
		for (let i = 0; i < likes.length; i++) likes[i].addEventListener("click", _like);
	})();
})();