-- SQL command to setup the forum.
-- Everything is generated with SQLite Database Browser except the comments.

-- Users
CREATE TABLE `users` (
	`userid`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`username`	TEXT NOT NULL UNIQUE,
	`password`	TEXT NOT NULL,
	`vip`	INTEGER NOT NULL DEFAULT 0,
	`gender`	TEXT NOT NULL DEFAULT 'other',
	`token`	TEXT NOT NULL UNIQUE,
	`registered`	INTEGER NOT NULL DEFAULT 0
);

-- Posts
CREATE TABLE `posts` (
	`postid`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`title`	TEXT NOT NULL,
	`content`	TEXT NOT NULL,
	`sticky`	INTEGER NOT NULL DEFAULT 0,
	`user`	INTEGER NOT NULL DEFAULT 0,
	`datetime`	INTEGER NOT NULL DEFAULT 0,
	`views`	INTEGER NOT NULL DEFAULT 0,
	`likes`	TEXT NOT NULL DEFAULT '[]',
	`replies`	INTEGER NOT NULL DEFAULT 0
);

-- Replies
CREATE TABLE `replies` (
	`replyid`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`post`	INTEGER NOT NULL DEFAULT 0,
	`content`	TEXT NOT NULL,
	`user`	INTEGER NOT NULL DEFAULT 0,
	`datetime`	TEXT NOT NULL DEFAULT 0,
	`likes`	TEXT NOT NULL DEFAULT '[]',
	`floor`	INTEGER NOT NULL DEFAULT 0,
	`repliedTo`	TEXT DEFAULT null
);