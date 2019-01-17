How tired it is of updating! So don't modify your own source code easily!

# 5 Minute Forum

5 Minute Forum is a simple forum written with PHP.

This document is still improving. When I learned more about English, I'll update this document again.

## Help! I don't understand English!

This resource code based a Chinese document : `project/introduction.html`. If you really don't understand English ~~(Or I wrote too many wrongs)~~ , please read this document.

## Why will I program this forum

Here are the reasons of why I program this fourm:

* To show you how excellent I am. (用中文说就是装X)
* To help you with the structure of a forum software.
* To help you build a forum.
* To record as a video and publish to Bilibili.
* To pratiece myself.
* To...

## Migrate from old version

Originally I don't want to open this category because no one who I found is using my forum. But after a deep thinking, I decieded to write here.

Please write a script yourself to realize this feature, or get help from me with migration.

## Features

* Use SQLite for database. (Used JSON in initial version)
* Post and comment.
* Like and reply to a comment.
* VIP users.
* Native PHP.

## Installtion

1. Download/Clone this repository.
2. Move files to the path of you want to install.
3. Access `http://name.domain/path/setup.php`. (setup.php)
4. Fill basic information and click "Setup" button.
5. WINNER WINNER CHICKEN DINNER!

## Don't use in production

I've told you this forum is to show how excellent I am. There are lots of found bugs at this forum:

* Doesn't support lots users access at same time.
* No administration console, so you must modify database to manage the forum.
* Doesn't support tags and categories, you can't category posts.
* Doesn't support multi-pages, if too many posts and replies existed, browser maybe crash. (No matter how your computer's performance amazing)
* No XSS protection, users will attack the forum by evil code.(e.g. steal user cookies with AJAX.)

You can improve this forum if you can.

## Contact Me

To contact me, add my QQ `2477819731` as your contact, or post at [my forum](https://forum.dffzmxj.com).