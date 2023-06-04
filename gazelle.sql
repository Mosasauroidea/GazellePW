SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE `gazelle`;

--
-- Table structure for table `activity`
--

DROP TABLE IF EXISTS `activity`;
CREATE TABLE `activity` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Text` varchar(255) NOT NULL,
  `Time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Display` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `api_applications`
--

DROP TABLE IF EXISTS `api_applications`;

CREATE TABLE `api_applications` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) NOT NULL,
  `Token` char(32) NOT NULL,
  `Name` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

--
-- Table structure for table `api_users`
--

DROP TABLE IF EXISTS `api_users`;

CREATE TABLE `api_users` (
  `UserID` int(10) NOT NULL,
  `AppID` int(10) NOT NULL,
  `Token` char(32) NOT NULL,
  `State` enum('0','1','2') NOT NULL DEFAULT '0',
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Access` text NOT NULL,
  PRIMARY KEY (`UserID`,`AppID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `applicant`
--

DROP TABLE IF EXISTS `applicant`;

CREATE TABLE `applicant` (
  `ID` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `RoleID` int(4) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `ThreadID` int(6) unsigned NOT NULL,
  `Body` text NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Resolved` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `RoleID` (`RoleID`) USING BTREE,
  KEY `ThreadID` (`ThreadID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  CONSTRAINT `applicant_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `applicant_role` (`ID`),
  CONSTRAINT `applicant_ibfk_2` FOREIGN KEY (`ThreadID`) REFERENCES `thread` (`ID`),
  CONSTRAINT `applicant_ibfk_3` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `applicant_role`
--

DROP TABLE IF EXISTS `applicant_role`;

CREATE TABLE `applicant_role` (
  `ID` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(40) NOT NULL,
  `Published` tinyint(4) NOT NULL DEFAULT '0',
  `Description` text NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  CONSTRAINT `applicant_role_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `apply_question`
--

DROP TABLE IF EXISTS `apply_question`;

CREATE TABLE `apply_question` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(16) NOT NULL,
  `iterm_id` bigint(20) NOT NULL,
  `sort` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `apply_question_answer`
--

DROP TABLE IF EXISTS `apply_question_answer`;

CREATE TABLE `apply_question_answer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `iterm_id` bigint(20) DEFAULT NULL,
  `answer` varchar(1024) DEFAULT NULL,
  `remark` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `apply_question_iterm`
--

DROP TABLE IF EXISTS `apply_question_iterm`;

CREATE TABLE `apply_question_iterm` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) DEFAULT NULL,
  `description` blob,
  `remark` varchar(1024) DEFAULT NULL,
  `type` tinyint(4) DEFAULT '0' COMMENT '0-?? 1-?? 2-??',
  `allow_empty` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `apply_user`
--

DROP TABLE IF EXISTS `apply_user`;

CREATE TABLE `apply_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `email` varchar(32) DEFAULT NULL,
  `question_code` varchar(16) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `IP` varchar(32) DEFAULT NULL,
  `status` int(11) DEFAULT '0' COMMENT '0-??? 1-?? 2-??',
  `check_id` bigint(20) DEFAULT NULL,
  `check_description` varchar(1024) DEFAULT NULL,
  `check_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `apply_user_answer`
--

DROP TABLE IF EXISTS `apply_user_answer`;

CREATE TABLE `apply_user_answer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `apply_id` bigint(20) DEFAULT NULL,
  `question_code` varchar(16) DEFAULT NULL,
  `iterm_id` bigint(20) DEFAULT NULL,
  `answer` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `artist_info_cache`
--

DROP TABLE IF EXISTS `artist_info_cache`;

CREATE TABLE `artist_info_cache` (
  `IMDBID` varchar(255) NOT NULL,
  `TMDBID` int(11) DEFAULT NULL,
  `TMDBData` longtext,
  `TMDBTime` datetime NOT NULL,
  PRIMARY KEY (`IMDBID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `artists_alias`
--

DROP TABLE IF EXISTS `artists_alias`;

CREATE TABLE `artists_alias` (
  `AliasID` int(10) NOT NULL AUTO_INCREMENT,
  `ArtistID` int(10) NOT NULL,
  `Name` varchar(200) DEFAULT NULL,
  `Redirect` int(10) NOT NULL DEFAULT '0',
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`AliasID`) USING BTREE,
  KEY `ArtistID` (`ArtistID`,`Name`) USING BTREE,
  KEY `Name` (`Name`),
  KEY `Redirect` (`Redirect`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `artists_group`
--

DROP TABLE IF EXISTS `artists_group`;

CREATE TABLE `artists_group` (
  `ArtistID` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) DEFAULT NULL,
  `RevisionID` int(12) DEFAULT NULL,
  `LastCommentID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ArtistID`) USING BTREE,
  KEY `Name` (`Name`,`RevisionID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `artists_similar`
--

DROP TABLE IF EXISTS `artists_similar`;

CREATE TABLE `artists_similar` (
  `ArtistID` int(10) NOT NULL DEFAULT '0',
  `SimilarID` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ArtistID`,`SimilarID`) USING BTREE,
  KEY `ArtistID` (`ArtistID`,`SimilarID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `artists_similar_scores`
--

DROP TABLE IF EXISTS `artists_similar_scores`;

CREATE TABLE `artists_similar_scores` (
  `SimilarID` int(12) NOT NULL AUTO_INCREMENT,
  `Score` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`SimilarID`) USING BTREE,
  KEY `Score` (`Score`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `artists_similar_votes`
--

DROP TABLE IF EXISTS `artists_similar_votes`;

CREATE TABLE `artists_similar_votes` (
  `SimilarID` int(12) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Way` enum('up','down') NOT NULL DEFAULT 'up',
  PRIMARY KEY (`SimilarID`,`UserID`,`Way`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `artists_tags`
--

DROP TABLE IF EXISTS `artists_tags`;

CREATE TABLE `artists_tags` (
  `TagID` int(10) NOT NULL DEFAULT '0',
  `ArtistID` int(10) NOT NULL DEFAULT '0',
  `PositiveVotes` int(6) NOT NULL DEFAULT '1',
  `NegativeVotes` int(6) NOT NULL DEFAULT '1',
  `UserID` int(10) NOT NULL,
  PRIMARY KEY (`TagID`,`ArtistID`) USING BTREE,
  KEY `TagID` (`TagID`,`ArtistID`,`PositiveVotes`,`NegativeVotes`,`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `bad_passwords`
--

DROP TABLE IF EXISTS `bad_passwords`;

CREATE TABLE `bad_passwords` (
  `Password` char(32) NOT NULL,
  PRIMARY KEY (`Password`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;

CREATE TABLE `badges` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `BadgeID` int(11) NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Username` int(11) NOT NULL DEFAULT '0',
  `Profile` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UserID` (`UserID`,`BadgeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `badges_item`
--

DROP TABLE IF EXISTS `badges_item`;

CREATE TABLE `badges_item` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Label` varchar(50) NOT NULL,
  `BigImage` varchar(255) NOT NULL,
  `SmallImage` varchar(255) NOT NULL,
  `Level` int(11) NOT NULL,
  `Count` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Label` (`Label`,`Level`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `badges_label`
--

DROP TABLE IF EXISTS `badges_label`;

CREATE TABLE `badges_label` (
  `Label` varchar(50) NOT NULL,
  `DisImage` varchar(255) NOT NULL,
  `Remark` varchar(255) NOT NULL,
  `Type` varchar(50) NOT NULL,
  `Auto` tinyint(1) NOT NULL DEFAULT '0',
  `Rank` int(11) NOT NULL,
  `Father` tinyint(1) NOT NULL DEFAULT '1',
  `Progress` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `blog`
--

DROP TABLE IF EXISTS `blog`;

CREATE TABLE `blog` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Body` text NOT NULL,
  `Time` datetime NOT NULL,
  `ThreadID` int(10) unsigned DEFAULT NULL,
  `Important` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `bonus_history`
--

DROP TABLE IF EXISTS `bonus_history`;

CREATE TABLE `bonus_history` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `ItemID` int(6) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `Price` int(10) unsigned NOT NULL,
  `OtherUserID` int(10) unsigned DEFAULT NULL,
  `PurchaseDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `bonus_history_fk_user` (`UserID`) USING BTREE,
  KEY `bonus_history_fk_item` (`ItemID`) USING BTREE,
  CONSTRAINT `bonus_history_fk_item` FOREIGN KEY (`ItemID`) REFERENCES `bonus_item` (`ID`),
  CONSTRAINT `bonus_history_fk_user` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `bonus_item`
--

DROP TABLE IF EXISTS `bonus_item`;

CREATE TABLE `bonus_item` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `Price` int(10) unsigned NOT NULL,
  `Amount` int(2) unsigned DEFAULT NULL,
  `MinClass` int(6) unsigned NOT NULL DEFAULT '0',
  `FreeClass` int(6) unsigned NOT NULL DEFAULT '999999',
  `OffPrice` int(10) NOT NULL,
  `OffClass` int(6) NOT NULL DEFAULT '999999',
  `Label` varchar(32) NOT NULL,
  `Title` varchar(64) NOT NULL,
  `Rank` int(6) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `Label` (`Label`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `bookmarks_artists`
--

DROP TABLE IF EXISTS `bookmarks_artists`;

CREATE TABLE `bookmarks_artists` (
  `UserID` int(10) NOT NULL,
  `ArtistID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `ArtistID` (`ArtistID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `bookmarks_collages`
--

DROP TABLE IF EXISTS `bookmarks_collages`;

CREATE TABLE `bookmarks_collages` (
  `UserID` int(10) NOT NULL,
  `CollageID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `CollageID` (`CollageID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `bookmarks_requests`
--

DROP TABLE IF EXISTS `bookmarks_requests`;

CREATE TABLE `bookmarks_requests` (
  `UserID` int(10) NOT NULL,
  `RequestID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `RequestID` (`RequestID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `bookmarks_torrents`
--

DROP TABLE IF EXISTS `bookmarks_torrents`;

CREATE TABLE `bookmarks_torrents` (
  `UserID` int(10) NOT NULL,
  `GroupID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  `Sort` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `groups_users` (`GroupID`,`UserID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `calendar`
--

DROP TABLE IF EXISTS `calendar`;

CREATE TABLE `calendar` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) DEFAULT NULL,
  `Body` mediumtext,
  `Category` tinyint(1) DEFAULT NULL,
  `StartDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `AddedBy` int(10) DEFAULT NULL,
  `Importance` tinyint(1) DEFAULT NULL,
  `Team` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `changelog`
--

DROP TABLE IF EXISTS `changelog`;

CREATE TABLE `changelog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Time` datetime NOT NULL,
  `Message` text NOT NULL,
  `Author` varchar(30) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `collages`
--

DROP TABLE IF EXISTS `collages`;

CREATE TABLE `collages` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL DEFAULT '',
  `Description` text NOT NULL,
  `UserID` int(10) NOT NULL DEFAULT '0',
  `NumTorrents` int(4) NOT NULL DEFAULT '0',
  `Deleted` enum('0','1') DEFAULT '0',
  `Locked` enum('0','1') NOT NULL DEFAULT '0',
  `CategoryID` int(2) NOT NULL DEFAULT '1',
  `TagList` varchar(500) NOT NULL DEFAULT '',
  `MaxGroups` int(10) NOT NULL DEFAULT '0',
  `MaxGroupsPerUser` int(10) NOT NULL DEFAULT '0',
  `Featured` tinyint(4) NOT NULL DEFAULT '0',
  `Subscribers` int(10) DEFAULT '0',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `Name` (`Name`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `CategoryID` (`CategoryID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `collages_artists`
--

DROP TABLE IF EXISTS `collages_artists`;

CREATE TABLE `collages_artists` (
  `CollageID` int(10) NOT NULL,
  `ArtistID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Sort` int(10) NOT NULL DEFAULT '0',
  `AddedOn` datetime NOT NULL,
  PRIMARY KEY (`CollageID`,`ArtistID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `Sort` (`Sort`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `collages_torrents`
--

DROP TABLE IF EXISTS `collages_torrents`;

CREATE TABLE `collages_torrents` (
  `CollageID` int(10) NOT NULL,
  `GroupID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Sort` int(10) NOT NULL DEFAULT '0',
  `AddedOn` datetime NOT NULL,
  PRIMARY KEY (`CollageID`,`GroupID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `Sort` (`Sort`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Page` enum('artist','collages','requests','torrents') NOT NULL,
  `PageID` int(10) NOT NULL,
  `AuthorID` int(10) NOT NULL,
  `AddedTime` datetime NOT NULL,
  `Body` mediumtext,
  `EditedUserID` int(10) DEFAULT NULL,
  `EditedTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Page` (`Page`,`PageID`) USING BTREE,
  KEY `AuthorID` (`AuthorID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `comments_edits`
--

DROP TABLE IF EXISTS `comments_edits`;

CREATE TABLE `comments_edits` (
  `Page` enum('forums','artist','collages','requests','torrents') DEFAULT NULL,
  `PostID` int(10) DEFAULT NULL,
  `EditUser` int(10) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  `Body` mediumtext,
  KEY `EditUser` (`EditUser`) USING BTREE,
  KEY `PostHistory` (`Page`,`PostID`,`EditTime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `comments_edits_tmp`
--

DROP TABLE IF EXISTS `comments_edits_tmp`;

CREATE TABLE `comments_edits_tmp` (
  `Page` enum('forums','artist','collages','requests','torrents') DEFAULT NULL,
  `PostID` int(10) DEFAULT NULL,
  `EditUser` int(10) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  `Body` mediumtext,
  KEY `EditUser` (`EditUser`) USING BTREE,
  KEY `PostHistory` (`Page`,`PostID`,`EditTime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `concerts`
--

DROP TABLE IF EXISTS `concerts`;

CREATE TABLE `concerts` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ConcertID` int(10) NOT NULL,
  `TopicID` int(10) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `ConcertID` (`ConcertID`) USING BTREE,
  KEY `TopicID` (`TopicID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `contest`
--

DROP TABLE IF EXISTS `contest`;

CREATE TABLE `contest` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ContestTypeID` int(11) NOT NULL,
  `Name` varchar(80)  NOT NULL,
  `Banner` varchar(128) NOT NULL DEFAULT '',
  `DateBegin` datetime NOT NULL,
  `DateEnd` datetime NOT NULL,
  `Display` int(11) NOT NULL DEFAULT '50',
  `MaxTracked` int(11) NOT NULL DEFAULT '500',
  `WikiText` mediumtext,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `Name` (`Name`) USING BTREE,
  KEY `contest_type_fk` (`ContestTypeID`) USING BTREE,
  CONSTRAINT `contest_type_fk` FOREIGN KEY (`ContestTypeID`) REFERENCES `contest_type` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `contest_leaderboard`
--

DROP TABLE IF EXISTS `contest_leaderboard`;

CREATE TABLE `contest_leaderboard` (
  `ContestID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `LastTorrentID` int(11) NOT NULL,
  `LastTorrentName` varchar(80)  NOT NULL,
  `ArtistList` varchar(80) NOT NULL,
  `ArtistNames` varchar(200) NOT NULL,
  `LastUpload` datetime NOT NULL,
  KEY `contest_fk` (`ContestID`) USING BTREE,
  CONSTRAINT `contest_fk` FOREIGN KEY (`ContestID`) REFERENCES `contest` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `contest_type`
--

DROP TABLE IF EXISTS `contest_type`;

CREATE TABLE `contest_type` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(32) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `Name` (`Name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  ;


--
-- Table structure for table `cover_art`
--

DROP TABLE IF EXISTS `cover_art`;

CREATE TABLE `cover_art` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `GroupID` int(10) NOT NULL,
  `Image` varchar(255) NOT NULL DEFAULT '',
  `Summary` varchar(100) DEFAULT NULL,
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Time` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `GroupID` (`GroupID`,`Image`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `currency_conversion_rates`
--

DROP TABLE IF EXISTS `currency_conversion_rates`;

CREATE TABLE `currency_conversion_rates` (
  `Currency` char(3) NOT NULL,
  `Rate` decimal(9,4) DEFAULT NULL,
  `Time` datetime DEFAULT NULL,
  PRIMARY KEY (`Currency`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `do_not_upload`
--

DROP TABLE IF EXISTS `do_not_upload`;

CREATE TABLE `do_not_upload` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Comment` varchar(255) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  `Sequence` mediumint(8) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `donations`
--

DROP TABLE IF EXISTS `donations`;

CREATE TABLE `donations` (
  `UserID` int(10) NOT NULL,
  `Amount` decimal(6,2) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Time` datetime NOT NULL,
  `Currency` varchar(5) NOT NULL DEFAULT 'USD',
  `Source` varchar(30) NOT NULL DEFAULT '',
  `Reason` mediumtext NOT NULL,
  `Rank` int(10) DEFAULT '0',
  `AddedBy` int(10) DEFAULT '0',
  `TotalRank` int(10) DEFAULT '0',
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE,
  KEY `Amount` (`Amount`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `donations_bitcoin`
--

DROP TABLE IF EXISTS `donations_bitcoin`;

CREATE TABLE `donations_bitcoin` (
  `BitcoinAddress` varchar(34) NOT NULL,
  `Amount` decimal(24,8) NOT NULL,
  KEY `BitcoinAddress` (`BitcoinAddress`,`Amount`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `donations_prepaid_card`
--

DROP TABLE IF EXISTS `donations_prepaid_card`;

CREATE TABLE `donations_prepaid_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `card_num` varchar(255) NOT NULL,
  `card_secret` varchar(255) NOT NULL,
  `face_value` varchar(255) NOT NULL,
  `status` enum('1','2','3') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `card_num` (`card_num`,`card_secret`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `donor_forum_usernames`
--

DROP TABLE IF EXISTS `donor_forum_usernames`;

CREATE TABLE `donor_forum_usernames` (
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Prefix` varchar(30) NOT NULL DEFAULT '',
  `Suffix` varchar(30) NOT NULL DEFAULT '',
  `UseComma` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `donor_rewards`
--

DROP TABLE IF EXISTS `donor_rewards`;

CREATE TABLE `donor_rewards` (
  `UserID` int(10) NOT NULL DEFAULT '0',
  `IconMouseOverText` varchar(200) NOT NULL DEFAULT '',
  `AvatarMouseOverText` varchar(200) NOT NULL DEFAULT '',
  `CustomIcon` varchar(200) NOT NULL DEFAULT '',
  `SecondAvatar` varchar(200) NOT NULL DEFAULT '',
  `CustomIconLink` varchar(200) NOT NULL DEFAULT '',
  `ProfileInfo1` text NOT NULL,
  `ProfileInfo2` text NOT NULL,
  `ProfileInfo3` text NOT NULL,
  `ProfileInfo4` text NOT NULL,
  `ProfileInfoTitle1` varchar(255) NOT NULL,
  `ProfileInfoTitle2` varchar(255) NOT NULL,
  `ProfileInfoTitle3` varchar(255) NOT NULL,
  `ProfileInfoTitle4` varchar(255) NOT NULL,
  `ColorUsername` varchar(45) DEFAULT NULL,
  `GradientsColor` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `dupe_groups`
--

DROP TABLE IF EXISTS `dupe_groups`;

CREATE TABLE `dupe_groups` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Comments` text,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `email_blacklist`
--

DROP TABLE IF EXISTS `email_blacklist`;

CREATE TABLE `email_blacklist` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Time` datetime NOT NULL,
  `Comment` text NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `events_reward_log`
--

DROP TABLE IF EXISTS `events_reward_log`;

CREATE TABLE `events_reward_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserIDs` text,
  `ByUserID` int(11) DEFAULT NULL,
  `Invites` int(11) DEFAULT NULL,
  `InvitesTime` datetime DEFAULT NULL,
  `Tokens` int(11) DEFAULT NULL,
  `TokensTime` datetime DEFAULT NULL,
  `Bonus` int(11) DEFAULT NULL,
  `Badge` int(11) DEFAULT NULL,
  `Remark` varchar(45) DEFAULT NULL,
  `Time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `featured_albums`
--

DROP TABLE IF EXISTS `featured_albums`;

CREATE TABLE `featured_albums` (
  `GroupID` int(10) NOT NULL DEFAULT '0',
  `ThreadID` int(10) NOT NULL DEFAULT '0',
  `Title` varchar(35) NOT NULL DEFAULT '',
  `Started` datetime NOT NULL,
  `Ended` datetime NOT NULL,
  `Type` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `featured_merch`
--

DROP TABLE IF EXISTS `featured_merch`;

CREATE TABLE `featured_merch` (
  `ProductID` int(10) NOT NULL DEFAULT '0',
  `Title` varchar(35) NOT NULL DEFAULT '',
  `Image` varchar(255) NOT NULL DEFAULT '',
  `Started` datetime NOT NULL,
  `Ended` datetime NOT NULL,
  `ArtistID` int(10) unsigned DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `forums`
--

DROP TABLE IF EXISTS `forums`;

CREATE TABLE `forums` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `CategoryID` tinyint(2) NOT NULL DEFAULT '0',
  `Sort` int(6) unsigned NOT NULL,
  `Name` varchar(40) NOT NULL DEFAULT '',
  `Description` varchar(255) DEFAULT '',
  `MinClassRead` int(4) NOT NULL DEFAULT '0',
  `MinClassWrite` int(4) NOT NULL DEFAULT '0',
  `MinClassCreate` int(4) NOT NULL DEFAULT '0',
  `NumTopics` int(10) NOT NULL DEFAULT '0',
  `NumPosts` int(10) NOT NULL DEFAULT '0',
  `LastPostID` int(10) NOT NULL DEFAULT '0',
  `LastPostAuthorID` int(10) NOT NULL DEFAULT '0',
  `LastPostTopicID` int(10) NOT NULL DEFAULT '0',
  `LastPostTime` datetime NOT NULL,
  `AutoLock` enum('0','1') DEFAULT '1',
  `AutoLockWeeks` int(3) unsigned NOT NULL DEFAULT '4',
  `Second` text NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Sort` (`Sort`) USING BTREE,
  KEY `MinClassRead` (`MinClassRead`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `forums_categories`
--

DROP TABLE IF EXISTS `forums_categories`;

CREATE TABLE `forums_categories` (
  `ID` tinyint(2) NOT NULL AUTO_INCREMENT,
  `Name` varchar(40) NOT NULL DEFAULT '',
  `Sort` int(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Sort` (`Sort`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `forums_last_read_topics`
--

DROP TABLE IF EXISTS `forums_last_read_topics`;

CREATE TABLE `forums_last_read_topics` (
  `UserID` int(10) NOT NULL,
  `TopicID` int(10) NOT NULL,
  `PostID` int(10) NOT NULL,
  PRIMARY KEY (`UserID`,`TopicID`) USING BTREE,
  KEY `TopicID` (`TopicID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `forums_polls`
--

DROP TABLE IF EXISTS `forums_polls`;

CREATE TABLE `forums_polls` (
  `TopicID` int(10) unsigned NOT NULL,
  `Question` varchar(255) NOT NULL,
  `Answers` text NOT NULL,
  `Featured` datetime NOT NULL,
  `Closed` enum('0','1') NOT NULL DEFAULT '0',
  `MaxCount` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`TopicID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `forums_polls_votes`
--

DROP TABLE IF EXISTS `forums_polls_votes`;

CREATE TABLE `forums_polls_votes` (
  `TopicID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `Vote` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`TopicID`,`UserID`,`Vote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `forums_posts`
--

DROP TABLE IF EXISTS `forums_posts`;

CREATE TABLE `forums_posts` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `TopicID` int(10) NOT NULL,
  `AuthorID` int(10) NOT NULL,
  `AddedTime` datetime NOT NULL,
  `Body` mediumtext,
  `EditedUserID` int(10) DEFAULT NULL,
  `EditedTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `TopicID` (`TopicID`) USING BTREE,
  KEY `AuthorID` (`AuthorID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `forums_posts_jf_log`
--

DROP TABLE IF EXISTS `forums_posts_jf_log`;

CREATE TABLE `forums_posts_jf_log` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `TopicID` int(10) NOT NULL,
  `AuthorID` int(10) NOT NULL,
  `PostID` int(10) NOT NULL,
  `AddedTime` datetime NOT NULL,
  `LogTime` datetime NOT NULL,
  `Sentuid` int(10) NOT NULL,
  `Sentjf` int(10) NOT NULL,
  `Comment` varchar(100) NOT NULL,
  `Sys` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `forums_specific_rules`
--

DROP TABLE IF EXISTS `forums_specific_rules`;

CREATE TABLE `forums_specific_rules` (
  `ForumID` int(6) unsigned DEFAULT NULL,
  `ThreadID` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `forums_topic_notes`
--

DROP TABLE IF EXISTS `forums_topic_notes`;

CREATE TABLE `forums_topic_notes` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `TopicID` int(10) NOT NULL,
  `AuthorID` int(10) NOT NULL,
  `AddedTime` datetime NOT NULL,
  `Body` mediumtext,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `TopicID` (`TopicID`) USING BTREE,
  KEY `AuthorID` (`AuthorID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `forums_topics`
--

DROP TABLE IF EXISTS `forums_topics`;

CREATE TABLE `forums_topics` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Title` varchar(150) NOT NULL,
  `AuthorID` int(10) NOT NULL,
  `IsLocked` enum('0','1') NOT NULL DEFAULT '0',
  `IsNotice` enum('0','1') NOT NULL DEFAULT '0',
  `IsSticky` enum('0','1') NOT NULL DEFAULT '0',
  `ForumID` int(3) NOT NULL,
  `NumPosts` int(10) NOT NULL DEFAULT '0',
  `LastPostID` int(10) NOT NULL,
  `LastPostTime` datetime NOT NULL,
  `LastPostAuthorID` int(10) NOT NULL,
  `StickyPostID` int(10) NOT NULL DEFAULT '0',
  `Ranking` tinyint(2) DEFAULT '0',
  `CreatedTime` datetime NOT NULL,
  `AutoLocked` enum('0','1','2') NOT NULL DEFAULT '0',
  `hiddenreplies` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `AuthorID` (`AuthorID`) USING BTREE,
  KEY `ForumID` (`ForumID`) USING BTREE,
  KEY `IsSticky` (`IsSticky`) USING BTREE,
  KEY `LastPostID` (`LastPostID`) USING BTREE,
  KEY `Title` (`Title`) USING BTREE,
  KEY `CreatedTime` (`CreatedTime`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `freetorrents_timed`
--

DROP TABLE IF EXISTS `freetorrents_timed`;

CREATE TABLE `freetorrents_timed` (
  `TorrentID` int(11) NOT NULL,
  `EndTime` datetime NOT NULL,
  PRIMARY KEY (`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;

CREATE TABLE `friends` (
  `UserID` int(10) unsigned NOT NULL,
  `FriendID` int(10) unsigned NOT NULL,
  `Comment` text NOT NULL,
  PRIMARY KEY (`UserID`,`FriendID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `FriendID` (`FriendID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `geoip_country`
--

DROP TABLE IF EXISTS `geoip_country`;

CREATE TABLE `geoip_country` (
  `StartIP` int(11) unsigned NOT NULL,
  `EndIP` int(11) unsigned NOT NULL,
  `Code` varchar(2) NOT NULL,
  PRIMARY KEY (`StartIP`,`EndIP`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `group_log`
--

DROP TABLE IF EXISTS `group_log`;

CREATE TABLE `group_log` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `GroupID` int(10) NOT NULL,
  `TorrentID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Info` mediumtext,
  `Time` datetime NOT NULL,
  `Hidden` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE,
  KEY `TorrentID` (`TorrentID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `invite_tree`
--

DROP TABLE IF EXISTS `invite_tree`;

CREATE TABLE `invite_tree` (
  `UserID` int(10) NOT NULL DEFAULT '0',
  `InviterID` int(10) NOT NULL DEFAULT '0',
  `TreePosition` int(8) NOT NULL DEFAULT '1',
  `TreeID` int(10) NOT NULL DEFAULT '1',
  `TreeLevel` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`) USING BTREE,
  KEY `InviterID` (`InviterID`) USING BTREE,
  KEY `TreePosition` (`TreePosition`) USING BTREE,
  KEY `TreeID` (`TreeID`) USING BTREE,
  KEY `TreeLevel` (`TreeLevel`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `invites`
--

DROP TABLE IF EXISTS `invites`;

CREATE TABLE `invites` (
  `InviterID` int(10) NOT NULL DEFAULT '0',
  `InviteKey` char(32) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Expires` datetime NOT NULL,
  `Reason` varchar(255) NOT NULL DEFAULT '',
  `InviteID` int(12) DEFAULT '0',
  PRIMARY KEY (`InviteKey`) USING BTREE,
  KEY `Expires` (`Expires`) USING BTREE,
  KEY `InviterID` (`InviterID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `invites_history`
--

DROP TABLE IF EXISTS `invites_history`;

CREATE TABLE `invites_history` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Email` varchar(255) NOT NULL,
  `InviteKey` char(32) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `invites_typed`
--

DROP TABLE IF EXISTS `invites_typed`;

CREATE TABLE `invites_typed` (
  `ID` int(12) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) NOT NULL,
  `EndTime` datetime DEFAULT NULL,
  `Type` enum('time','count') NOT NULL,
  `Used` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `ip_bans`
--

DROP TABLE IF EXISTS `ip_bans`;

CREATE TABLE `ip_bans` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FromIP` int(11) unsigned NOT NULL,
  `ToIP` int(11) unsigned NOT NULL,
  `Reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `FromIP_2` (`FromIP`,`ToIP`) USING BTREE,
  KEY `ToIP` (`ToIP`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `ip_lock`
--

DROP TABLE IF EXISTS `ip_lock`;

CREATE TABLE `ip_lock` (
  `UserID` int(11) NOT NULL,
  `IPs` varchar(150) NOT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `last_sent_email`
--

DROP TABLE IF EXISTS `last_sent_email`;

CREATE TABLE `last_sent_email` (
  `UserID` int(10) NOT NULL,
  PRIMARY KEY (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `locked_accounts`
--

DROP TABLE IF EXISTS `locked_accounts`;

CREATE TABLE `locked_accounts` (
  `UserID` int(10) unsigned NOT NULL,
  `Type` tinyint(1) NOT NULL,
  PRIMARY KEY (`UserID`) USING BTREE,
  CONSTRAINT `fk_user_id` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;

CREATE TABLE `log` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Message` varchar(400) NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;

CREATE TABLE `login_attempts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `IP` varchar(15) NOT NULL,
  `LastAttempt` datetime NOT NULL,
  `Attempts` int(10) unsigned NOT NULL,
  `BannedUntil` datetime NOT NULL,
  `Bans` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `IP` (`IP`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `login_link`
--

DROP TABLE IF EXISTS `login_link`;

CREATE TABLE `login_link` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LoginKey` char(32) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Username` varchar(20) NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Used` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `movie_info_cache`
--

DROP TABLE IF EXISTS `movie_info_cache`;

CREATE TABLE `movie_info_cache` (
  `IMDBID` varchar(15) NOT NULL,
  `OMDBData` json DEFAULT NULL,
  `Time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `TMDBData` longtext,
  `IMDBActorData` longtext,
  `DoubanActorData` longtext,
  `DoubanData` longtext,
  `DoubanTime` datetime NOT NULL,
  `DoubanActorTime` datetime NOT NULL,
  `IMDBActorTime` datetime NOT NULL,
  `TMDBTime` datetime NOT NULL,
  `OMDBTime` datetime NOT NULL,
  `DoubanID` int(11) DEFAULT NULL,
  `TMDBID` int(11) DEFAULT NULL,
  PRIMARY KEY (`IMDBID`),
  KEY `DoubanID` (`DoubanID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `new_info_hashes`
--

DROP TABLE IF EXISTS `new_info_hashes`;

CREATE TABLE `new_info_hashes` (
  `TorrentID` int(11) NOT NULL,
  `InfoHash` binary(20) DEFAULT NULL,
  PRIMARY KEY (`TorrentID`) USING BTREE,
  KEY `InfoHash` (`InfoHash`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;

CREATE TABLE `news` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Body` text NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `ocelot_query_times`
--

DROP TABLE IF EXISTS `ocelot_query_times`;

CREATE TABLE `ocelot_query_times` (
  `buffer` enum('users','torrents','snatches','peers') NOT NULL,
  `starttime` datetime NOT NULL,
  `ocelotinstance` datetime NOT NULL,
  `querylength` int(11) NOT NULL,
  `timespent` int(11) NOT NULL,
  UNIQUE KEY `starttime` (`starttime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Level` int(10) unsigned NOT NULL,
  `Name` varchar(25) NOT NULL,
  `Values` text NOT NULL,
  `DisplayStaff` enum('0','1') NOT NULL DEFAULT '0',
  `PermittedForums` varchar(150) NOT NULL DEFAULT '',
  `Secondary` tinyint(4) NOT NULL DEFAULT '0',
  `StaffGroup` int(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `Level` (`Level`) USING BTREE,
  KEY `DisplayStaff` (`DisplayStaff`) USING BTREE,
  KEY `StaffGroup` (`StaffGroup`) USING BTREE,
  CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`StaffGroup`) REFERENCES `staff_groups` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `phinxlog`
--

DROP TABLE IF EXISTS `phinxlog`;

CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `pm_conversations`
--

DROP TABLE IF EXISTS `pm_conversations`;

CREATE TABLE `pm_conversations` (
  `ID` int(12) NOT NULL AUTO_INCREMENT,
  `Subject` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `pm_conversations_users`
--

DROP TABLE IF EXISTS `pm_conversations_users`;

CREATE TABLE `pm_conversations_users` (
  `UserID` int(10) NOT NULL DEFAULT '0',
  `ConvID` int(12) NOT NULL DEFAULT '0',
  `InInbox` enum('1','0') NOT NULL,
  `InSentbox` enum('1','0') NOT NULL,
  `SentDate` datetime NOT NULL,
  `ReceivedDate` datetime NOT NULL,
  `UnRead` enum('1','0') NOT NULL DEFAULT '1',
  `Sticky` enum('1','0') NOT NULL DEFAULT '0',
  `ForwardedTo` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`,`ConvID`) USING BTREE,
  KEY `InInbox` (`InInbox`) USING BTREE,
  KEY `InSentbox` (`InSentbox`) USING BTREE,
  KEY `ConvID` (`ConvID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `SentDate` (`SentDate`) USING BTREE,
  KEY `ReceivedDate` (`ReceivedDate`) USING BTREE,
  KEY `Sticky` (`Sticky`) USING BTREE,
  KEY `ForwardedTo` (`ForwardedTo`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `pm_messages`
--

DROP TABLE IF EXISTS `pm_messages`;

CREATE TABLE `pm_messages` (
  `ID` int(12) NOT NULL AUTO_INCREMENT,
  `ConvID` int(12) NOT NULL DEFAULT '0',
  `SentDate` datetime NOT NULL,
  `SenderID` int(10) NOT NULL DEFAULT '0',
  `Body` text,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `ConvID` (`ConvID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `push_notifications_usage`
--

DROP TABLE IF EXISTS `push_notifications_usage`;

CREATE TABLE `push_notifications_usage` (
  `PushService` varchar(10) NOT NULL,
  `TimesUsed` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`PushService`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `register_apply`
--

DROP TABLE IF EXISTS `register_apply`;

CREATE TABLE `register_apply` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `email` tinytext NOT NULL,
  `site` text NOT NULL,
  `ipv4` char(15) DEFAULT NULL,
  `ipv6` char(63) DEFAULT NULL,
  `site_ss` text NOT NULL,
  `client_ss` text NOT NULL,
  `introduction` text NOT NULL,
  `apply_status` int(1) unsigned zerofill NOT NULL DEFAULT '0',
  `apply_pw` char(32) NOT NULL,
  `note` text,
  `waring` text,
  `ts` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `ts_mod` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `id_mod` int(10) DEFAULT NULL,
  `addnote` text,
  `c_red` int(1) unsigned zerofill NOT NULL,
  `c_ops` int(1) unsigned zerofill NOT NULL,
  `c_nwcd` int(1) unsigned zerofill NOT NULL,
  `c_opencd` int(1) unsigned zerofill NOT NULL,
  `c_others` int(1) unsigned zerofill NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `register_apply_link`
--

DROP TABLE IF EXISTS `register_apply_link`;

CREATE TABLE `register_apply_link` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(255) NOT NULL,
  `IP` varchar(40) NOT NULL,
  `ApplyKey` char(32) NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Used` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ApplyKey` (`ApplyKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `register_apply_log`
--

DROP TABLE IF EXISTS `register_apply_log`;

CREATE TABLE `register_apply_log` (
  `UserID` int(10) NOT NULL,
  `ApplyID` int(10) NOT NULL,
  `ApplyStatus` int(1) NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;

CREATE TABLE `reports` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  `ThingID` int(10) unsigned NOT NULL DEFAULT '0',
  `Type` varchar(30) DEFAULT NULL,
  `Comment` text,
  `ResolverID` int(10) unsigned NOT NULL DEFAULT '0',
  `Status` enum('New','InProgress','Resolved') DEFAULT 'New',
  `ResolvedTime` datetime NOT NULL,
  `ReportedTime` datetime NOT NULL,
  `Reason` text NOT NULL,
  `ClaimerID` int(10) unsigned NOT NULL DEFAULT '0',
  `Notes` text NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Status` (`Status`) USING BTREE,
  KEY `Type` (`Type`) USING BTREE,
  KEY `ResolvedTime` (`ResolvedTime`) USING BTREE,
  KEY `ResolverID` (`ResolverID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `reports_email_blacklist`
--

DROP TABLE IF EXISTS `reports_email_blacklist`;

CREATE TABLE `reports_email_blacklist` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Type` tinyint(4) NOT NULL DEFAULT '0',
  `UserID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  `Checked` tinyint(4) NOT NULL DEFAULT '0',
  `ResolverID` int(10) DEFAULT '0',
  `Email` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `reportsv2`
--

DROP TABLE IF EXISTS `reportsv2`;

CREATE TABLE `reportsv2` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ReporterID` int(10) unsigned NOT NULL DEFAULT '0',
  `TorrentID` int(10) unsigned NOT NULL DEFAULT '0',
  `Type` varchar(255) NOT NULL,
  `UserComment` text,
  `ResolverID` int(10) unsigned NOT NULL DEFAULT '0',
  `Status` enum('New','InProgress','Resolved') DEFAULT 'New',
  `ReportedTime` datetime NOT NULL,
  `LastChangeTime` datetime NOT NULL,
  `ModComment` text,
  `Track` text,
  `Image` text,
  `ExtraID` text,
  `Link` text,
  `LogMessage` text,
  `UploaderReply` text,
  `ReplyTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Status` (`Status`) USING BTREE,
  KEY `Type` (`Type`(1)) USING BTREE,
  KEY `LastChangeTime` (`LastChangeTime`) USING BTREE,
  KEY `TorrentID` (`TorrentID`) USING BTREE,
  KEY `ResolverID` (`ResolverID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;

CREATE TABLE `requests` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeAdded` datetime NOT NULL,
  `LastVote` datetime DEFAULT NULL,
  `CategoryID` int(3) NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Year` int(4) DEFAULT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `Description` text NOT NULL,
  `ReleaseType` tinyint(2) DEFAULT NULL,
  `FillerID` int(10) unsigned NOT NULL DEFAULT '0',
  `TorrentID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeFilled` datetime NOT NULL,
  `Visible` binary(1) NOT NULL DEFAULT '1',
  `GroupID` int(10) DEFAULT NULL,
  `CodecList` varchar(255) NOT NULL,
  `SourceList` varchar(255) NOT NULL,
  `ContainerList` varchar(255) NOT NULL,
  `ResolutionList` varchar(255) NOT NULL,
  `IMDBID` varchar(255) NOT NULL,
  `Subtitle` varchar(255) NOT NULL,
  `SourceTorrent` varchar(255) NOT NULL,
  `PurchasableAt` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Userid` (`UserID`) USING BTREE,
  KEY `Name` (`Title`) USING BTREE,
  KEY `Filled` (`TorrentID`) USING BTREE,
  KEY `FillerID` (`FillerID`) USING BTREE,
  KEY `TimeAdded` (`TimeAdded`) USING BTREE,
  KEY `Year` (`Year`) USING BTREE,
  KEY `TimeFilled` (`TimeFilled`) USING BTREE,
  KEY `LastVote` (`LastVote`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `requests_artists`
--

DROP TABLE IF EXISTS `requests_artists`;

CREATE TABLE `requests_artists` (
  `RequestID` int(10) unsigned NOT NULL,
  `ArtistID` int(10) NOT NULL,
  `AliasID` int(10) NOT NULL,
  `Importance` enum('1','2','3','4','5','6','7') DEFAULT NULL,
  PRIMARY KEY (`RequestID`,`AliasID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `requests_tags`
--

DROP TABLE IF EXISTS `requests_tags`;

CREATE TABLE `requests_tags` (
  `TagID` int(10) NOT NULL DEFAULT '0',
  `RequestID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`TagID`,`RequestID`) USING BTREE,
  KEY `TagID` (`TagID`) USING BTREE,
  KEY `RequestID` (`RequestID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `requests_votes`
--

DROP TABLE IF EXISTS `requests_votes`;

CREATE TABLE `requests_votes` (
  `RequestID` int(10) NOT NULL DEFAULT '0',
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Bounty` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`RequestID`,`UserID`) USING BTREE,
  KEY `RequestID` (`RequestID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `Bounty` (`Bounty`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;

CREATE TABLE `schedule` (
  `NextHour` int(2) NOT NULL DEFAULT '0',
  `NextDay` int(2) NOT NULL DEFAULT '0',
  `NextBiWeekly` int(2) NOT NULL DEFAULT '0',
  `NextMonth` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `site_history`
--

DROP TABLE IF EXISTS `site_history`;

CREATE TABLE `site_history` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) DEFAULT NULL,
  `Url` varchar(255) NOT NULL DEFAULT '',
  `Category` tinyint(2) DEFAULT NULL,
  `SubCategory` tinyint(2) DEFAULT NULL,
  `Tags` mediumtext,
  `AddedBy` int(10) DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  `Body` mediumtext,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `site_options`
--

DROP TABLE IF EXISTS `site_options`;

CREATE TABLE `site_options` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(64) NOT NULL,
  `Value` tinytext NOT NULL,
  `Comment` text NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `Name` (`Name`) USING BTREE,
  KEY `name_index` (`Name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `sphinx_a`
--

DROP TABLE IF EXISTS `sphinx_a`;

CREATE TABLE `sphinx_a` (
  `gid` int(11) DEFAULT NULL,
  `aname` text,
  KEY `gid` (`gid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `sphinx_delta`
--

DROP TABLE IF EXISTS `sphinx_delta`;

CREATE TABLE `sphinx_delta` (
  `ID` int(10) NOT NULL,
  `GroupID` int(11) NOT NULL DEFAULT '0',
  `GroupName` varchar(255) DEFAULT NULL,
  `ArtistName` varchar(2048) DEFAULT NULL,
  `TagList` varchar(728) DEFAULT NULL,
  `Year` int(4) DEFAULT NULL,
  `CategoryID` tinyint(2) DEFAULT NULL,
  `Time` int(12) DEFAULT NULL,
  `ReleaseType` tinyint(2) DEFAULT NULL,
  `Size` bigint(20) DEFAULT NULL,
  `Snatched` int(10) DEFAULT NULL,
  `Seeders` int(10) DEFAULT NULL,
  `Leechers` int(10) DEFAULT NULL,
  `Scene` tinyint(1) NOT NULL DEFAULT '0',
  `Jinzhuan` tinyint(1) NOT NULL DEFAULT '0',
  `Diy` tinyint(1) NOT NULL DEFAULT '0',
  `Buy` tinyint(1) NOT NULL DEFAULT '0',
  `Allow` tinyint(1) NOT NULL DEFAULT '0',
  `FreeTorrent` tinyint(1) DEFAULT NULL,
  `FileList` mediumtext,
  `Description` text,
  `VoteScore` float NOT NULL DEFAULT '0',
  `LastChanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IMDBRating` float DEFAULT NULL,
  `DoubanRating` float DEFAULT NULL,
  `Region` varchar(100) DEFAULT NULL,
  `Language` varchar(100) DEFAULT NULL,
  `IMDBID` varchar(15) DEFAULT NULL,
  `Resolution` varchar(15) DEFAULT NULL,
  `Container` varchar(15) DEFAULT NULL,
  `Source` varchar(15) DEFAULT NULL,
  `codec` varchar(15) NOT NULL,
  `Subtitles` set('chinese_simplified','chinese_traditional','english','japanese','korean','no_subtitles','arabic','brazilian_port','bulgarian','croatian','czech','danish','dutch','estonian','finnish','french','german','greek','hebrew','hindi','hungarian','icelandic','indonesian','italian','latvian','lithuanian','norwegian','persian','polish','portuguese','romanian','russian','serbian','slovak','slovenian','spanish','swedish','thai','turkish','ukrainian','vietnamese') DEFAULT NULL,
  `RTRating` varchar(255) NOT NULL,
  `Processing` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE,
  KEY `Size` (`Size`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `sphinx_hash`
--

DROP TABLE IF EXISTS `sphinx_hash`;

CREATE TABLE `sphinx_hash` (
  `ID` int(10) NOT NULL,
  `GroupName` varchar(255) DEFAULT NULL,
  `ArtistName` varchar(2048) DEFAULT NULL,
  `TagList` varchar(728) DEFAULT NULL,
  `Year` int(4) DEFAULT NULL,
  `CategoryID` tinyint(2) DEFAULT NULL,
  `Time` int(12) DEFAULT NULL,
  `ReleaseType` tinyint(2) DEFAULT NULL,
  `Size` bigint(20) DEFAULT NULL,
  `Snatched` int(10) DEFAULT NULL,
  `Seeders` int(10) DEFAULT NULL,
  `Leechers` int(10) DEFAULT NULL,
  `Scene` tinyint(1) NOT NULL DEFAULT '0',
  `FreeTorrent` tinyint(1) DEFAULT NULL,
  `RemasterYear` int(4) DEFAULT NULL,
  `RemasterTitle` varchar(512) DEFAULT NULL,
  `FileList` mediumtext,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `sphinx_index_last_pos`
--

DROP TABLE IF EXISTS `sphinx_index_last_pos`;

CREATE TABLE `sphinx_index_last_pos` (
  `Type` varchar(16) NOT NULL DEFAULT '',
  `ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `sphinx_requests`
--

DROP TABLE IF EXISTS `sphinx_requests`;

CREATE TABLE `sphinx_requests` (
  `ID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeAdded` int(12) unsigned NOT NULL,
  `LastVote` int(12) unsigned NOT NULL,
  `CategoryID` int(3) NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Year` int(4) DEFAULT NULL,
  `ArtistList` varchar(2048) DEFAULT NULL,
  `ReleaseType` tinyint(2) DEFAULT NULL,
  `FillerID` int(10) unsigned NOT NULL DEFAULT '0',
  `TorrentID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeFilled` int(12) unsigned NOT NULL,
  `Visible` binary(1) NOT NULL DEFAULT '1',
  `Bounty` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Votes` int(10) unsigned NOT NULL DEFAULT '0',
  `CodecList` varchar(255) NOT NULL,
  `SourceList` varchar(255) NOT NULL,
  `ContainerList` varchar(255) NOT NULL,
  `ResolutionList` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Userid` (`UserID`) USING BTREE,
  KEY `Name` (`Title`) USING BTREE,
  KEY `Filled` (`TorrentID`) USING BTREE,
  KEY `FillerID` (`FillerID`) USING BTREE,
  KEY `TimeAdded` (`TimeAdded`) USING BTREE,
  KEY `Year` (`Year`) USING BTREE,
  KEY `TimeFilled` (`TimeFilled`) USING BTREE,
  KEY `LastVote` (`LastVote`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `sphinx_requests_delta`
--

DROP TABLE IF EXISTS `sphinx_requests_delta`;

CREATE TABLE `sphinx_requests_delta` (
  `ID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeAdded` int(12) unsigned DEFAULT NULL,
  `LastVote` int(12) unsigned DEFAULT NULL,
  `CategoryID` tinyint(4) DEFAULT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `TagList` varchar(728) NOT NULL DEFAULT '',
  `Year` int(4) DEFAULT NULL,
  `ArtistList` varchar(2048) DEFAULT NULL,
  `ReleaseType` tinyint(2) DEFAULT NULL,
  `FillerID` int(10) unsigned NOT NULL DEFAULT '0',
  `TorrentID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeFilled` int(12) unsigned DEFAULT NULL,
  `Visible` binary(1) NOT NULL DEFAULT '1',
  `Bounty` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Votes` int(10) unsigned NOT NULL DEFAULT '0',
  `CodecList` varchar(255) NOT NULL,
  `SourceList` varchar(255) NOT NULL,
  `ContainerList` varchar(255) NOT NULL,
  `ResolutionList` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Userid` (`UserID`) USING BTREE,
  KEY `Name` (`Title`) USING BTREE,
  KEY `Filled` (`TorrentID`) USING BTREE,
  KEY `FillerID` (`FillerID`) USING BTREE,
  KEY `TimeAdded` (`TimeAdded`) USING BTREE,
  KEY `Year` (`Year`) USING BTREE,
  KEY `TimeFilled` (`TimeFilled`) USING BTREE,
  KEY `LastVote` (`LastVote`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `sphinx_t`
--

DROP TABLE IF EXISTS `sphinx_t`;

CREATE TABLE `sphinx_t` (
  `id` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `size` bigint(20) NOT NULL,
  `snatched` int(11) NOT NULL,
  `seeders` int(11) NOT NULL,
  `leechers` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `scene` tinyint(4) NOT NULL,
  `jinzhuan` tinyint(4) NOT NULL,
  `diy` tinyint(4) NOT NULL,
  `buy` tinyint(4) NOT NULL,
  `allow` tinyint(4) NOT NULL,
  `freetorrent` tinyint(4) NOT NULL,
  `resolution` varchar(15) DEFAULT NULL,
  `maker` varchar(15) DEFAULT NULL,
  `container` varchar(15) DEFAULT NULL,
  `subtitle` varchar(80) NOT NULL DEFAULT '',
  `codec` varchar(15) NOT NULL,
  `source` varchar(15) DEFAULT NULL,
  `remyear` smallint(6) NOT NULL,
  `remtitle` varchar(80) NOT NULL,
  `filelist` mediumtext,
  `description` text,
  `subtitles` set('chinese_simplified','chinese_traditional','english','japanese','korean','no_subtitles','arabic','brazilian_port','bulgarian','croatian','czech','danish','dutch','estonian','finnish','french','german','greek','hebrew','hindi','hungarian','icelandic','indonesian','italian','latvian','lithuanian','norwegian','persian','polish','portuguese','romanian','russian','serbian','slovak','slovenian','spanish','swedish','thai','turkish','ukrainian','vietnamese') DEFAULT NULL,
  `processing` varchar(255) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `gid_remident` (`gid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `sphinx_tg`
--

DROP TABLE IF EXISTS `sphinx_tg`;

CREATE TABLE `sphinx_tg` (
  `id` int(11) NOT NULL,
  `name` varchar(300) DEFAULT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `year` smallint(6) DEFAULT NULL,
  `catid` smallint(6) DEFAULT NULL,
  `reltype` smallint(6) DEFAULT NULL,
  `subname` varchar(300) DEFAULT NULL,
  `imdbid` varchar(15) DEFAULT NULL,
  `imdbrating` float DEFAULT NULL,
  `doubanrating` float DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `language` varchar(100) DEFAULT NULL,
  `rtrating` varchar(255) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `staff_answers`
--

DROP TABLE IF EXISTS `staff_answers`;

CREATE TABLE `staff_answers` (
  `QuestionID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Answer` mediumtext,
  `Date` datetime NOT NULL,
  PRIMARY KEY (`QuestionID`,`UserID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `staff_blog`
--

DROP TABLE IF EXISTS `staff_blog`;

CREATE TABLE `staff_blog` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Body` text NOT NULL,
  `Time` datetime NOT NULL,
  `ThreadID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `staff_blog_visits`
--

DROP TABLE IF EXISTS `staff_blog_visits`;

CREATE TABLE `staff_blog_visits` (
  `UserID` int(10) unsigned NOT NULL,
  `Time` datetime NOT NULL,
  UNIQUE KEY `UserID` (`UserID`) USING BTREE,
  CONSTRAINT `staff_blog_visits_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `staff_groups`
--

DROP TABLE IF EXISTS `staff_groups`;

CREATE TABLE `staff_groups` (
  `ID` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `Sort` int(4) unsigned NOT NULL,
  `Name` text NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `Name` (`Name`(50)) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `staff_ignored_questions`
--

DROP TABLE IF EXISTS `staff_ignored_questions`;

CREATE TABLE `staff_ignored_questions` (
  `QuestionID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  PRIMARY KEY (`QuestionID`,`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `staff_pm_conversations`
--

DROP TABLE IF EXISTS `staff_pm_conversations`;

CREATE TABLE `staff_pm_conversations` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Subject` text,
  `UserID` int(11) DEFAULT NULL,
  `Status` enum('Open','Unanswered','Resolved') DEFAULT NULL,
  `Level` int(11) DEFAULT NULL,
  `AssignedToUser` int(11) DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  `Unread` tinyint(1) DEFAULT NULL,
  `ResolverID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `StatusAssigned` (`Status`,`AssignedToUser`) USING BTREE,
  KEY `StatusLevel` (`Status`,`Level`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `staff_pm_messages`
--

DROP TABLE IF EXISTS `staff_pm_messages`;

CREATE TABLE `staff_pm_messages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `SentDate` datetime DEFAULT NULL,
  `Message` text,
  `ConvID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `staff_pm_responses`
--

DROP TABLE IF EXISTS `staff_pm_responses`;

CREATE TABLE `staff_pm_responses` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Message` text,
  `Name` text,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `styles_backup`
--

DROP TABLE IF EXISTS `styles_backup`;

CREATE TABLE `styles_backup` (
  `UserID` int(10) NOT NULL DEFAULT '0',
  `StyleID` int(10) DEFAULT NULL,
  `StyleURL` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`UserID`) USING BTREE,
  KEY `StyleURL` (`StyleURL`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `stylesheets`
--

DROP TABLE IF EXISTS `stylesheets`;

CREATE TABLE `stylesheets` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `Default` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `subtitles`
--

DROP TABLE IF EXISTS `subtitles`;

CREATE TABLE `subtitles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `languages` varchar(255) NOT NULL,
  `torrent_id` int(11) NOT NULL,
  `source` varchar(255) NOT NULL,
  `download_times` varchar(255) NOT NULL,
  `format` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `uploader` int(11) NOT NULL,
  `upload_time` datetime NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `torrent_id` (`torrent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `subtitles_files`
--

DROP TABLE IF EXISTS `subtitles_files`;

CREATE TABLE `subtitles_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `File` mediumblob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `tag_aliases`
--

DROP TABLE IF EXISTS `tag_aliases`;

CREATE TABLE `tag_aliases` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `BadTag` varchar(30) DEFAULT NULL,
  `AliasTag` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `BadTag` (`BadTag`) USING BTREE,
  KEY `AliasTag` (`AliasTag`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) DEFAULT NULL,
  `TagType` enum('genre','other') NOT NULL DEFAULT 'other',
  `Uses` int(12) NOT NULL DEFAULT '1',
  `UserID` int(10) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `Name_2` (`Name`) USING BTREE,
  KEY `TagType` (`TagType`) USING BTREE,
  KEY `Uses` (`Uses`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `thread`
--

DROP TABLE IF EXISTS `thread`;

CREATE TABLE `thread` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `ThreadTypeID` int(6) unsigned NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `ThreadTypeID` (`ThreadTypeID`) USING BTREE,
  CONSTRAINT `thread_ibfk_1` FOREIGN KEY (`ThreadTypeID`) REFERENCES `thread_type` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `thread_note`
--

DROP TABLE IF EXISTS `thread_note`;

CREATE TABLE `thread_note` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `ThreadID` int(6) unsigned NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `UserID` int(10) unsigned NOT NULL,
  `Body` mediumtext NOT NULL,
  `Visibility` enum('staff','public') NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `ThreadID` (`ThreadID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  CONSTRAINT `thread_note_ibfk_1` FOREIGN KEY (`ThreadID`) REFERENCES `thread` (`ID`),
  CONSTRAINT `thread_note_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `thread_type`
--

DROP TABLE IF EXISTS `thread_type`;

CREATE TABLE `thread_type` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `Name` (`Name`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `thumb`
--

DROP TABLE IF EXISTS `thumb`;

CREATE TABLE `thumb` (
  `ItemID` int(10) NOT NULL,
  `Type` enum('post','profile','wiki','torrent') NOT NULL,
  `FromUserID` int(10) NOT NULL,
  `ToUserID` int(10) NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ItemID`,`Type`,`FromUserID`,`ToUserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `tokens_typed`
--

DROP TABLE IF EXISTS `tokens_typed`;

CREATE TABLE `tokens_typed` (
  `ID` int(12) NOT NULL AUTO_INCREMENT,
  `EndTime` date DEFAULT NULL,
  `Type` enum('count','time') NOT NULL,
  `UserID` int(10) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `top10_history`
--

DROP TABLE IF EXISTS `top10_history`;

CREATE TABLE `top10_history` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Date` datetime NOT NULL,
  `Type` enum('Daily','Weekly') DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `top10_history_torrents`
--

DROP TABLE IF EXISTS `top10_history_torrents`;

CREATE TABLE `top10_history_torrents` (
  `HistoryID` int(10) NOT NULL DEFAULT '0',
  `Rank` tinyint(2) NOT NULL DEFAULT '0',
  `TorrentID` int(10) NOT NULL DEFAULT '0',
  `TitleString` varchar(150) NOT NULL DEFAULT '',
  `TagString` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents`
--

DROP TABLE IF EXISTS `torrents`;

CREATE TABLE `torrents` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `GroupID` int(10) NOT NULL,
  `UserID` int(10) DEFAULT NULL,
  `RemasterYear` int(4) DEFAULT NULL,
  `RemasterTitle` varchar(255) NOT NULL,
  `Scene` enum('0','1') NOT NULL DEFAULT '0',
  `Jinzhuan` enum('0','1') NOT NULL DEFAULT '0',
  `Diy` enum('0','1') NOT NULL DEFAULT '0',
  `Buy` enum('0','1') NOT NULL DEFAULT '0',
  `Allow` enum('0','1') NOT NULL DEFAULT '0',
  `info_hash` blob NOT NULL,
  `FileCount` int(6) NOT NULL,
  `FileList` mediumtext NOT NULL,
  `FilePath` varchar(255) NOT NULL DEFAULT '',
  `Size` bigint(12) NOT NULL,
  `Leechers` int(6) NOT NULL DEFAULT '0',
  `Seeders` int(6) NOT NULL DEFAULT '0',
  `last_action` datetime NOT NULL,
  `FreeTorrent` enum('0','1','2','11','12','13') NOT NULL DEFAULT '0',
  `FreeLeechType` enum('0','1','2','3','4','5','6','7') NOT NULL DEFAULT '0',
  `Time` datetime NOT NULL,
  `Description` text,
  `Snatched` int(10) unsigned NOT NULL DEFAULT '0',
  `balance` bigint(20) NOT NULL DEFAULT '0',
  `LastReseedRequest` datetime NOT NULL,
  `Checked` int(10) NOT NULL,
  `NotMainMovie` enum('0','1') DEFAULT '0',
  `Source` varchar(10) DEFAULT NULL,
  `Codec` varchar(10) DEFAULT NULL,
  `Container` varchar(10) DEFAULT NULL,
  `Resolution` varchar(10) DEFAULT NULL,
  `Subtitles` set('chinese_simplified','chinese_traditional','english','japanese','korean','no_subtitles','arabic','brazilian_port','bulgarian','croatian','czech','danish','dutch','estonian','finnish','french','german','greek','hebrew','hindi','hungarian','icelandic','indonesian','italian','latvian','lithuanian','norwegian','persian','polish','portuguese','romanian','russian','serbian','slovak','slovenian','spanish','swedish','thai','turkish','ukrainian','vietnamese') DEFAULT NULL,
  `Makers` varchar(20) DEFAULT NULL,
  `dead_time` datetime NOT NULL,
  `RemasterCustomTitle` varchar(255) NOT NULL,
  `Processing` varchar(255) NOT NULL,
  `ChineseDubbed` tinyint(1) NOT NULL,
  `SpecialSub` tinyint(1) NOT NULL,
  `MediaInfo` mediumtext NOT NULL,
  `Note` text NOT NULL,
  `SubtitleType` int(11) NOT NULL,
  `Slot` int(11) NOT NULL,
  `IsExtraSlot` tinyint(1) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `InfoHash` (`info_hash`(40)) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `Year` (`RemasterYear`) USING BTREE,
  KEY `FileCount` (`FileCount`) USING BTREE,
  KEY `Size` (`Size`) USING BTREE,
  KEY `Seeders` (`Seeders`) USING BTREE,
  KEY `Leechers` (`Leechers`) USING BTREE,
  KEY `Snatched` (`Snatched`) USING BTREE,
  KEY `last_action` (`last_action`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE,
  KEY `FreeTorrent` (`FreeTorrent`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_artists`
--

DROP TABLE IF EXISTS `torrents_artists`;

CREATE TABLE `torrents_artists` (
  `GroupID` int(10) NOT NULL,
  `ArtistID` int(10) NOT NULL,
  `AliasID` int(10) NOT NULL,
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  `Importance` enum('1','2','3','4','5','6','7') NOT NULL DEFAULT '1',
  `Credit` tinyint(1) NOT NULL DEFAULT '0',
  `Order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`GroupID`,`ArtistID`,`Importance`) USING BTREE,
  KEY `ArtistID` (`ArtistID`) USING BTREE,
  KEY `AliasID` (`AliasID`) USING BTREE,
  KEY `Importance` (`Importance`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_bad_files`
--

DROP TABLE IF EXISTS `torrents_bad_files`;

CREATE TABLE `torrents_bad_files` (
  `TorrentID` int(11) NOT NULL DEFAULT '0',
  `UserID` int(11) NOT NULL DEFAULT '0',
  `TimeAdded` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_bad_folders`
--

DROP TABLE IF EXISTS `torrents_bad_folders`;

CREATE TABLE `torrents_bad_folders` (
  `TorrentID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `TimeAdded` datetime NOT NULL,
  PRIMARY KEY (`TorrentID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_balance_history`
--

DROP TABLE IF EXISTS `torrents_balance_history`;

CREATE TABLE `torrents_balance_history` (
  `TorrentID` int(10) NOT NULL,
  `GroupID` int(10) NOT NULL,
  `balance` bigint(20) NOT NULL,
  `Time` datetime NOT NULL,
  `Last` enum('0','1','2') DEFAULT '0',
  UNIQUE KEY `TorrentID_2` (`TorrentID`,`Time`) USING BTREE,
  UNIQUE KEY `TorrentID_3` (`TorrentID`,`balance`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_check`
--

DROP TABLE IF EXISTS `torrents_check`;

CREATE TABLE `torrents_check` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) NOT NULL,
  `TorrentID` int(10) NOT NULL,
  `Type` int(1) NOT NULL,
  `Message` varchar(250) DEFAULT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `torrents_custom_trumpable`
--

DROP TABLE IF EXISTS `torrents_custom_trumpable`;

CREATE TABLE `torrents_custom_trumpable` (
  `TorrentID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `TimeAdded` datetime NOT NULL,
  `CustomTrumpable` text NOT NULL,
  PRIMARY KEY (`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `torrents_files`
--

DROP TABLE IF EXISTS `torrents_files`;

CREATE TABLE `torrents_files` (
  `TorrentID` int(10) NOT NULL,
  `File` mediumblob NOT NULL,
  PRIMARY KEY (`TorrentID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_group`
--

DROP TABLE IF EXISTS `torrents_group`;

CREATE TABLE `torrents_group` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `ArtistID` int(10) DEFAULT NULL,
  `CategoryID` int(3) DEFAULT NULL,
  `Name` varchar(300) DEFAULT NULL,
  `SubName` varchar(300) DEFAULT NULL,
  `Year` int(4) DEFAULT NULL,
  `ReleaseType` tinyint(2) DEFAULT '21',
  `TagList` varchar(500) NOT NULL,
  `Time` datetime NOT NULL,
  `RevisionID` int(12) DEFAULT NULL,
  `WikiBody` text NOT NULL,
  `WikiImage` varchar(255) NOT NULL,
  `IMDBID` varchar(15) DEFAULT NULL,
  `TrailerLink` varchar(45) DEFAULT NULL,
  `IMDBRating` float DEFAULT NULL,
  `DoubanRating` float DEFAULT NULL,
  `Duration` smallint(2) DEFAULT NULL,
  `ReleaseDate` varchar(15) DEFAULT NULL,
  `Region` varchar(100) DEFAULT NULL,
  `Language` varchar(100) DEFAULT NULL,
  `RTRating` varchar(255) DEFAULT NULL,
  `DoubanID` int(11) DEFAULT NULL,
  `DoubanVote` int(11) DEFAULT NULL,
  `IMDBVote` int(11) DEFAULT NULL,
  `RTTitle` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `ArtistID` (`ArtistID`) USING BTREE,
  KEY `CategoryID` (`CategoryID`) USING BTREE,
  KEY `Name` (`Name`(255)) USING BTREE,
  KEY `Year` (`Year`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE,
  KEY `RevisionID` (`RevisionID`) USING BTREE,
  KEY `IMDBID` (`IMDBID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_hard_sub`
--

DROP TABLE IF EXISTS `torrents_hard_sub`;

CREATE TABLE `torrents_hard_sub` (
  `TorrentID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `TimeAdded` datetime NOT NULL,
  PRIMARY KEY (`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `torrents_no_sub`
--

DROP TABLE IF EXISTS `torrents_no_sub`;

CREATE TABLE `torrents_no_sub` (
  `TorrentID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `TimeAdded` datetime NOT NULL,
  PRIMARY KEY (`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `torrents_peerlists`
--

DROP TABLE IF EXISTS `torrents_peerlists`;

CREATE TABLE `torrents_peerlists` (
  `TorrentID` int(11) NOT NULL,
  `GroupID` int(11) DEFAULT NULL,
  `Seeders` int(11) DEFAULT NULL,
  `Leechers` int(11) DEFAULT NULL,
  `Snatches` int(11) DEFAULT NULL,
  PRIMARY KEY (`TorrentID`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE,
  KEY `Stats` (`TorrentID`,`Seeders`,`Leechers`,`Snatches`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_peerlists_compare`
--

DROP TABLE IF EXISTS `torrents_peerlists_compare`;

CREATE TABLE `torrents_peerlists_compare` (
  `TorrentID` int(11) NOT NULL,
  `GroupID` int(11) DEFAULT NULL,
  `Seeders` int(11) DEFAULT NULL,
  `Leechers` int(11) DEFAULT NULL,
  `Snatches` int(11) DEFAULT NULL,
  PRIMARY KEY (`TorrentID`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE,
  KEY `Stats` (`TorrentID`,`Seeders`,`Leechers`,`Snatches`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_recommended`
--

DROP TABLE IF EXISTS `torrents_recommended`;

CREATE TABLE `torrents_recommended` (
  `GroupID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`GroupID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_send_bonus`
--

DROP TABLE IF EXISTS `torrents_send_bonus`;

CREATE TABLE `torrents_send_bonus` (
  `TorrentID` int(11) NOT NULL,
  `FromUserID` int(11) NOT NULL,
  `Bonus` int(11) NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`TorrentID`,`FromUserID`,`Bonus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `torrents_tags`
--

DROP TABLE IF EXISTS `torrents_tags`;

CREATE TABLE `torrents_tags` (
  `TagID` int(10) NOT NULL DEFAULT '0',
  `GroupID` int(10) NOT NULL DEFAULT '0',
  `PositiveVotes` int(6) NOT NULL DEFAULT '1',
  `NegativeVotes` int(6) NOT NULL DEFAULT '1',
  `UserID` int(10) DEFAULT NULL,
  PRIMARY KEY (`TagID`,`GroupID`) USING BTREE,
  KEY `TagID` (`TagID`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE,
  KEY `PositiveVotes` (`PositiveVotes`) USING BTREE,
  KEY `NegativeVotes` (`NegativeVotes`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_tags_votes`
--

DROP TABLE IF EXISTS `torrents_tags_votes`;

CREATE TABLE `torrents_tags_votes` (
  `GroupID` int(10) NOT NULL,
  `TagID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Way` enum('up','down') NOT NULL DEFAULT 'up',
  PRIMARY KEY (`GroupID`,`TagID`,`UserID`,`Way`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `torrents_votes`
--

DROP TABLE IF EXISTS `torrents_votes`;

CREATE TABLE `torrents_votes` (
  `GroupID` int(10) NOT NULL,
  `Ups` int(10) unsigned NOT NULL DEFAULT '0',
  `Total` int(10) unsigned NOT NULL DEFAULT '0',
  `Score` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`GroupID`) USING BTREE,
  KEY `Score` (`Score`) USING BTREE,
  CONSTRAINT `torrents_votes_ibfk_1` FOREIGN KEY (`GroupID`) REFERENCES `torrents_group` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `upload_contest`
--

DROP TABLE IF EXISTS `upload_contest`;

CREATE TABLE `upload_contest` (
  `TorrentID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`TorrentID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  CONSTRAINT `upload_contest_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `user_questions`
--

DROP TABLE IF EXISTS `user_questions`;

CREATE TABLE `user_questions` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Question` mediumtext NOT NULL,
  `UserID` int(10) NOT NULL,
  `Date` datetime NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Date` (`Date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_collage_subs`
--

DROP TABLE IF EXISTS `users_collage_subs`;

CREATE TABLE `users_collage_subs` (
  `UserID` int(10) NOT NULL,
  `CollageID` int(10) NOT NULL,
  `LastVisit` datetime DEFAULT NULL,
  PRIMARY KEY (`UserID`,`CollageID`) USING BTREE,
  KEY `CollageID` (`CollageID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_comments_last_read`
--

DROP TABLE IF EXISTS `users_comments_last_read`;

CREATE TABLE `users_comments_last_read` (
  `UserID` int(10) NOT NULL,
  `Page` enum('artist','collages','requests','torrents') NOT NULL,
  `PageID` int(10) NOT NULL,
  `PostID` int(10) NOT NULL,
  PRIMARY KEY (`UserID`,`Page`,`PageID`) USING BTREE,
  KEY `Page` (`Page`,`PageID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_donor_ranks`
--

DROP TABLE IF EXISTS `users_donor_ranks`;

CREATE TABLE `users_donor_ranks` (
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Rank` tinyint(2) NOT NULL DEFAULT '0',
  `Foundrank` tinyint(2) NOT NULL DEFAULT '0',
  `DonationTime` datetime DEFAULT NULL,
  `FoundTime` datetime DEFAULT NULL,
  `Hidden` tinyint(2) NOT NULL DEFAULT '0',
  `TotalRank` int(10) NOT NULL DEFAULT '0',
  `SpecialRank` tinyint(2) DEFAULT '0',
  `InvitesRecievedRank` tinyint(4) DEFAULT '0',
  `RankExpirationTime` datetime DEFAULT NULL,
  `FoundExpirationTime` datetime DEFAULT NULL,
  PRIMARY KEY (`UserID`) USING BTREE,
  KEY `DonationTime` (`DonationTime`) USING BTREE,
  KEY `SpecialRank` (`SpecialRank`) USING BTREE,
  KEY `Rank` (`Rank`) USING BTREE,
  KEY `TotalRank` (`TotalRank`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_downloads`
--

DROP TABLE IF EXISTS `users_downloads`;

CREATE TABLE `users_downloads` (
  `UserID` int(10) NOT NULL,
  `TorrentID` int(1) NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`UserID`,`TorrentID`,`Time`) USING BTREE,
  KEY `TorrentID` (`TorrentID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_dupes`
--

DROP TABLE IF EXISTS `users_dupes`;

CREATE TABLE `users_dupes` (
  `GroupID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  UNIQUE KEY `UserID` (`UserID`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE,
  CONSTRAINT `users_dupes_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `users_dupes_ibfk_2` FOREIGN KEY (`GroupID`) REFERENCES `dupe_groups` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_enable_recommendations`
--

DROP TABLE IF EXISTS `users_enable_recommendations`;

CREATE TABLE `users_enable_recommendations` (
  `ID` int(10) NOT NULL,
  `Enable` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `Enable` (`Enable`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_enable_requests`
--

DROP TABLE IF EXISTS `users_enable_requests`;

CREATE TABLE `users_enable_requests` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Email` varchar(255) NOT NULL,
  `IP` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `UserAgent` text NOT NULL,
  `Timestamp` datetime NOT NULL,
  `HandledTimestamp` datetime DEFAULT NULL,
  `Token` char(32) DEFAULT NULL,
  `CheckedBy` int(10) unsigned DEFAULT NULL,
  `Outcome` tinyint(1) DEFAULT NULL COMMENT '1 for approved, 2 for denied, 3 for discarded',
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `UserId` (`UserID`) USING BTREE,
  KEY `CheckedBy` (`CheckedBy`) USING BTREE,
  CONSTRAINT `users_enable_requests_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`),
  CONSTRAINT `users_enable_requests_ibfk_2` FOREIGN KEY (`CheckedBy`) REFERENCES `users_main` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_freeleeches`
--

DROP TABLE IF EXISTS `users_freeleeches`;

CREATE TABLE `users_freeleeches` (
  `UserID` int(10) NOT NULL,
  `TorrentID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  `Expired` tinyint(1) NOT NULL DEFAULT '0',
  `Downloaded` bigint(20) NOT NULL DEFAULT '0',
  `Uses` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`UserID`,`TorrentID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE,
  KEY `Expired_Time` (`Expired`,`Time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_freeleeches_time`
--

DROP TABLE IF EXISTS `users_freeleeches_time`;

CREATE TABLE `users_freeleeches_time` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `TorrentID` int(11) NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `users_freetorrents`
--

DROP TABLE IF EXISTS `users_freetorrents`;

CREATE TABLE `users_freetorrents` (
  `UserID` int(10) NOT NULL,
  `TorrentID` int(10) NOT NULL,
  `FreeTorrent` enum('0','1','2','11','12','13') NOT NULL DEFAULT '0',
  `Time` datetime NOT NULL,
  `Uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`,`TorrentID`,`FreeTorrent`),
  KEY `Time` (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_geodistribution`
--

DROP TABLE IF EXISTS `users_geodistribution`;

CREATE TABLE `users_geodistribution` (
  `Code` varchar(2) NOT NULL,
  `Users` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_history_emails`
--

DROP TABLE IF EXISTS `users_history_emails`;

CREATE TABLE `users_history_emails` (
  `UserID` int(10) NOT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Time` datetime DEFAULT NULL,
  `IP` varchar(15) DEFAULT NULL,
  KEY `UserID` (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_history_ips`
--

DROP TABLE IF EXISTS `users_history_ips`;

CREATE TABLE `users_history_ips` (
  `UserID` int(10) NOT NULL,
  `IP` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `StartTime` datetime NOT NULL,
  `EndTime` datetime DEFAULT NULL,
  PRIMARY KEY (`UserID`,`IP`,`StartTime`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `IP` (`IP`) USING BTREE,
  KEY `StartTime` (`StartTime`) USING BTREE,
  KEY `EndTime` (`EndTime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_history_passkeys`
--

DROP TABLE IF EXISTS `users_history_passkeys`;

CREATE TABLE `users_history_passkeys` (
  `UserID` int(10) NOT NULL,
  `OldPassKey` varchar(32) DEFAULT NULL,
  `NewPassKey` varchar(32) DEFAULT NULL,
  `ChangeTime` datetime DEFAULT NULL,
  `ChangerIP` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_history_passwords`
--

DROP TABLE IF EXISTS `users_history_passwords`;

CREATE TABLE `users_history_passwords` (
  `UserID` int(10) NOT NULL,
  `ChangeTime` datetime DEFAULT NULL,
  `ChangerIP` varchar(15) DEFAULT NULL,
  KEY `User_Time` (`UserID`,`ChangeTime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_info`
--

DROP TABLE IF EXISTS `users_info`;

CREATE TABLE `users_info` (
  `UserID` int(10) unsigned NOT NULL,
  `StyleID` int(10) unsigned NOT NULL,
  `StyleURL` varchar(255) DEFAULT NULL,
  `Info` text NOT NULL,
  `Avatar` varchar(255) NOT NULL,
  `AdminComment` text NOT NULL,
  `SiteOptions` text NOT NULL,
  `ViewAvatars` enum('0','1') NOT NULL DEFAULT '1',
  `Donor` enum('0','1') NOT NULL DEFAULT '0',
  `Found` enum('0','1') NOT NULL DEFAULT '0',
  `Artist` enum('0','1') NOT NULL DEFAULT '0',
  `DownloadAlt` enum('0','1') NOT NULL DEFAULT '0',
  `Warned` datetime NOT NULL,
  `SupportFor` varchar(255) NOT NULL,
  `TorrentGrouping` enum('0','1','2') NOT NULL COMMENT '0=Open,1=Closed,2=Off',
  `NotifyOnQuote` enum('0','1','2') NOT NULL DEFAULT '0',
  `AuthKey` varchar(32) NOT NULL,
  `ResetKey` varchar(32) NOT NULL,
  `ResetExpires` datetime NOT NULL,
  `JoinDate` datetime NOT NULL,
  `Inviter` int(10) DEFAULT NULL,
  `BitcoinAddress` varchar(34) DEFAULT NULL,
  `WarnedTimes` int(2) NOT NULL DEFAULT '0',
  `DisableAvatar` enum('0','1') NOT NULL DEFAULT '0',
  `DisableInvites` enum('0','1') NOT NULL DEFAULT '0',
  `DisablePosting` enum('0','1') NOT NULL DEFAULT '0',
  `DisableForums` enum('0','1') NOT NULL DEFAULT '0',
  `DisablePoints` enum('0','1') NOT NULL DEFAULT '0',
  `DisableIRC` enum('0','1') DEFAULT '0',
  `DisableTagging` enum('0','1') NOT NULL DEFAULT '0',
  `DisableUpload` enum('0','1') NOT NULL DEFAULT '0',
  `DisableWiki` enum('0','1') NOT NULL DEFAULT '0',
  `DisablePM` enum('0','1') NOT NULL DEFAULT '0',
  `RatioWatchEnds` datetime NOT NULL,
  `RatioWatchDownload` bigint(20) unsigned NOT NULL DEFAULT '0',
  `RatioWatchTimes` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `BanDate` datetime NOT NULL,
  `BanReason` enum('0','1','2','3','4') NOT NULL DEFAULT '0',
  `CatchupTime` datetime DEFAULT NULL,
  `LastReadNews` int(10) NOT NULL DEFAULT '0',
  `HideCountryChanges` enum('0','1') NOT NULL DEFAULT '0',
  `RestrictedForums` varchar(150) NOT NULL DEFAULT '',
  `DisableRequests` enum('0','1') NOT NULL DEFAULT '0',
  `PermittedForums` varchar(150) NOT NULL DEFAULT '',
  `UnseededAlerts` enum('0','1') NOT NULL DEFAULT '0',
  `ReportedAlerts` enum('0','1') NOT NULL DEFAULT '1',
  `RequestsAlerts` enum('0','1') NOT NULL DEFAULT '1',
  `LastReadBlog` int(10) NOT NULL DEFAULT '0',
  `InfoTitle` varchar(255) NOT NULL,
  `NotifyOnDeleteSeeding` enum('0','1') NOT NULL DEFAULT '1',
  `NotifyOnDeleteSnatched` enum('0','1') NOT NULL DEFAULT '1',
  `NotifyOnDeleteDownloaded` enum('0','1') NOT NULL DEFAULT '1',
  `Lang` varchar(16) DEFAULT 'chs',
  `DisableCheckAll` enum('0','1') NOT NULL DEFAULT '0',
  `DisableCheckSelf` enum('0','1') NOT NULL DEFAULT '0',
  `TGID` varchar(15) DEFAULT NULL,
  UNIQUE KEY `UserID` (`UserID`) USING BTREE,
  KEY `SupportFor` (`SupportFor`) USING BTREE,
  KEY `DisableInvites` (`DisableInvites`) USING BTREE,
  KEY `Donor` (`Donor`) USING BTREE,
  KEY `Warned` (`Warned`) USING BTREE,
  KEY `JoinDate` (`JoinDate`) USING BTREE,
  KEY `Inviter` (`Inviter`) USING BTREE,
  KEY `RatioWatchEnds` (`RatioWatchEnds`) USING BTREE,
  KEY `RatioWatchDownload` (`RatioWatchDownload`) USING BTREE,
  KEY `BitcoinAddress` (`BitcoinAddress`(4)) USING BTREE,
  KEY `AuthKey` (`AuthKey`) USING BTREE,
  KEY `ResetKey` (`ResetKey`) USING BTREE,
  KEY `Found` (`Found`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_last_month`
--

DROP TABLE IF EXISTS `users_last_month`;

CREATE TABLE `users_last_month` (
  `ID` int(10) unsigned NOT NULL,
  `Downloaded` bigint(20) unsigned NOT NULL,
  `TorrentCnt` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `users_levels`
--

DROP TABLE IF EXISTS `users_levels`;

CREATE TABLE `users_levels` (
  `UserID` int(10) unsigned NOT NULL,
  `PermissionID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`UserID`,`PermissionID`) USING BTREE,
  KEY `PermissionID` (`PermissionID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_main`
--

DROP TABLE IF EXISTS `users_main`;

CREATE TABLE `users_main` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Username` varchar(20) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `PassHash` varchar(60) NOT NULL,
  `Secret` char(32) DEFAULT NULL,
  `IRCKey` char(32) DEFAULT NULL,
  `LastLogin` datetime NOT NULL,
  `LastAccess` datetime NOT NULL,
  `IP` varchar(40) NOT NULL DEFAULT '0.0.0.0',
  `Class` tinyint(2) NOT NULL DEFAULT '5',
  `Uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `BonusPoints` float(20,5) NOT NULL DEFAULT '0.00000',
  `Title` text NOT NULL,
  `Enabled` enum('0','1','2') NOT NULL DEFAULT '0',
  `Paranoia` text,
  `Visible` enum('1','0') NOT NULL DEFAULT '1',
  `Invites` int(10) unsigned NOT NULL DEFAULT '0',
  `PermissionID` int(10) unsigned NOT NULL,
  `AwardLevel` int(11) NOT NULL DEFAULT '0',
  `CustomPermissions` text,
  `can_leech` tinyint(4) NOT NULL DEFAULT '1',
  `torrent_pass` char(32) NOT NULL,
  `RequiredRatio` double(10,8) NOT NULL DEFAULT '0.00000000',
  `RequiredRatioWork` double(10,8) NOT NULL DEFAULT '0.00000000',
  `ipcc` varchar(2) NOT NULL DEFAULT '',
  `FLTokens` int(10) NOT NULL DEFAULT '0',
  `FLT_Given` int(10) NOT NULL DEFAULT '0',
  `Invites_Given` int(10) NOT NULL DEFAULT '0',
  `2FA_Key` varchar(16) DEFAULT NULL,
  `Recovery` text,
  `FirstTorrent` int(10) NOT NULL DEFAULT '0',
  `TotalUploads` int(11) NOT NULL DEFAULT '0' COMMENT '总发种数',
  `BonusUploaded` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `Username` (`Username`) USING BTREE,
  KEY `Email` (`Email`) USING BTREE,
  KEY `PassHash` (`PassHash`) USING BTREE,
  KEY `LastAccess` (`LastAccess`) USING BTREE,
  KEY `IP` (`IP`) USING BTREE,
  KEY `Class` (`Class`) USING BTREE,
  KEY `Uploaded` (`Uploaded`) USING BTREE,
  KEY `Downloaded` (`Downloaded`) USING BTREE,
  KEY `Enabled` (`Enabled`) USING BTREE,
  KEY `Invites` (`Invites`) USING BTREE,
  KEY `torrent_pass` (`torrent_pass`) USING BTREE,
  KEY `RequiredRatio` (`RequiredRatio`) USING BTREE,
  KEY `cc_index` (`ipcc`) USING BTREE,
  KEY `PermissionID` (`PermissionID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_notifications_settings`
--

DROP TABLE IF EXISTS `users_notifications_settings`;

CREATE TABLE `users_notifications_settings` (
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Inbox` tinyint(1) DEFAULT '1',
  `StaffPM` tinyint(1) DEFAULT '1',
  `News` tinyint(1) DEFAULT '1',
  `Blog` tinyint(1) DEFAULT '1',
  `Torrents` tinyint(1) DEFAULT '1',
  `Collages` tinyint(1) DEFAULT '1',
  `Quotes` tinyint(1) DEFAULT '1',
  `Subscriptions` tinyint(1) DEFAULT '1',
  `SiteAlerts` tinyint(1) DEFAULT '1',
  `RequestAlerts` tinyint(1) DEFAULT '1',
  `CollageAlerts` tinyint(1) DEFAULT '1',
  `TorrentAlerts` tinyint(1) DEFAULT '1',
  `ForumAlerts` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_notify_filters`
--

DROP TABLE IF EXISTS `users_notify_filters`;

CREATE TABLE `users_notify_filters` (
  `ID` int(12) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) NOT NULL,
  `Label` varchar(128) NOT NULL DEFAULT '',
  `Artists` mediumtext NOT NULL,
  `RecordLabels` mediumtext NOT NULL,
  `Users` mediumtext NOT NULL,
  `Tags` varchar(500) NOT NULL DEFAULT '',
  `NotTags` varchar(500) NOT NULL DEFAULT '',
  `Categories` varchar(500) NOT NULL DEFAULT '',
  `Formats` varchar(500) NOT NULL DEFAULT '',
  `Encodings` varchar(500) NOT NULL DEFAULT '',
  `Media` varchar(500) NOT NULL DEFAULT '',
  `FromYear` int(4) NOT NULL DEFAULT '0',
  `ToYear` int(4) NOT NULL DEFAULT '0',
  `ExcludeVA` enum('1','0') NOT NULL DEFAULT '0',
  `NewGroupsOnly` enum('1','0') NOT NULL DEFAULT '0',
  `ReleaseTypes` varchar(500) NOT NULL DEFAULT '',
  `FromLogScore` int(6) NOT NULL DEFAULT '0',
  `ToLogScore` int(6) NOT NULL DEFAULT '0',
  `FromSize` bigint(12) NOT NULL DEFAULT '0',
  `ToSize` bigint(12) NOT NULL DEFAULT '0',
  `NotUsers` mediumtext NOT NULL,
  `Codecs` varchar(255) NOT NULL,
  `Resolutions` varchar(255) NOT NULL,
  `Sources` varchar(255) NOT NULL,
  `Containers` varchar(255) NOT NULL,
  `FromIMDBRating` int(11) NOT NULL,
  `Regions` varchar(255) NOT NULL,
  `Languages` varchar(255) NOT NULL,
  `RemasterTitles` varchar(255) NOT NULL,
  `FreeTorrents` varchar(255) NOT NULL,
  `Processings` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `FromYear` (`FromYear`) USING BTREE,
  KEY `ToYear` (`ToYear`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_notify_quoted`
--

DROP TABLE IF EXISTS `users_notify_quoted`;

CREATE TABLE `users_notify_quoted` (
  `UserID` int(10) NOT NULL,
  `QuoterID` int(10) NOT NULL,
  `Page` enum('forums','artist','collages','requests','torrents') NOT NULL,
  `PageID` int(10) NOT NULL,
  `PostID` int(10) NOT NULL,
  `UnRead` tinyint(1) NOT NULL DEFAULT '1',
  `Date` datetime NOT NULL,
  PRIMARY KEY (`UserID`,`Page`,`PostID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_notify_torrents`
--

DROP TABLE IF EXISTS `users_notify_torrents`;

CREATE TABLE `users_notify_torrents` (
  `UserID` int(10) NOT NULL,
  `FilterID` int(10) NOT NULL,
  `GroupID` int(10) NOT NULL,
  `TorrentID` int(10) NOT NULL,
  `UnRead` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`UserID`,`TorrentID`) USING BTREE,
  KEY `TorrentID` (`TorrentID`) USING BTREE,
  KEY `UserID_Unread` (`UserID`,`UnRead`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_points`
--

DROP TABLE IF EXISTS `users_points`;

CREATE TABLE `users_points` (
  `UserID` int(10) NOT NULL,
  `GroupID` int(10) NOT NULL,
  `Points` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`UserID`,`GroupID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_points_requests`
--

DROP TABLE IF EXISTS `users_points_requests`;

CREATE TABLE `users_points_requests` (
  `UserID` int(10) NOT NULL,
  `RequestID` int(10) NOT NULL,
  `Points` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`RequestID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `RequestID` (`RequestID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_push_notifications`
--

DROP TABLE IF EXISTS `users_push_notifications`;

CREATE TABLE `users_push_notifications` (
  `UserID` int(10) NOT NULL,
  `PushService` tinyint(1) NOT NULL DEFAULT '0',
  `PushOptions` text NOT NULL,
  PRIMARY KEY (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_sessions`
--

DROP TABLE IF EXISTS `users_sessions`;

CREATE TABLE `users_sessions` (
  `UserID` int(10) NOT NULL,
  `SessionID` char(32) NOT NULL,
  `KeepLogged` enum('0','1') NOT NULL DEFAULT '0',
  `Browser` varchar(40) DEFAULT NULL,
  `OperatingSystem` varchar(13) DEFAULT NULL,
  `IP` varchar(15) NOT NULL,
  `LastUpdate` datetime NOT NULL,
  `Active` tinyint(4) NOT NULL DEFAULT '1',
  `FullUA` text,
  `BrowserVersion` varchar(40) DEFAULT NULL,
  `OperatingSystemVersion` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`UserID`,`SessionID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `LastUpdate` (`LastUpdate`) USING BTREE,
  KEY `Active` (`Active`) USING BTREE,
  KEY `ActiveAgeKeep` (`Active`,`LastUpdate`,`KeepLogged`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_subscriptions`
--

DROP TABLE IF EXISTS `users_subscriptions`;

CREATE TABLE `users_subscriptions` (
  `UserID` int(10) NOT NULL,
  `TopicID` int(10) NOT NULL,
  PRIMARY KEY (`UserID`,`TopicID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_subscriptions_comments`
--

DROP TABLE IF EXISTS `users_subscriptions_comments`;

CREATE TABLE `users_subscriptions_comments` (
  `UserID` int(10) NOT NULL,
  `Page` enum('artist','collages','requests','torrents') NOT NULL,
  `PageID` int(10) NOT NULL,
  PRIMARY KEY (`UserID`,`Page`,`PageID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_summary`
--

DROP TABLE IF EXISTS `users_summary`;

CREATE TABLE `users_summary` (
  `UserID` int(10) unsigned NOT NULL,
  `Groups` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`),
  CONSTRAINT `users_summary_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `users_torrent_history`
--

DROP TABLE IF EXISTS `users_torrent_history`;

CREATE TABLE `users_torrent_history` (
  `UserID` int(10) unsigned NOT NULL,
  `NumTorrents` int(6) unsigned NOT NULL,
  `Date` int(8) unsigned NOT NULL,
  `Time` int(11) unsigned NOT NULL DEFAULT '0',
  `LastTime` int(11) unsigned NOT NULL DEFAULT '0',
  `Finished` enum('1','0') NOT NULL DEFAULT '1',
  `Weight` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`,`NumTorrents`,`Date`) USING BTREE,
  KEY `Finished` (`Finished`) USING BTREE,
  KEY `Date` (`Date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_torrent_history_snatch`
--

DROP TABLE IF EXISTS `users_torrent_history_snatch`;

CREATE TABLE `users_torrent_history_snatch` (
  `UserID` int(10) unsigned NOT NULL,
  `NumSnatches` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`) USING BTREE,
  KEY `NumSnatches` (`NumSnatches`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_torrent_history_temp`
--

DROP TABLE IF EXISTS `users_torrent_history_temp`;

CREATE TABLE `users_torrent_history_temp` (
  `UserID` int(10) unsigned NOT NULL,
  `NumTorrents` int(6) unsigned NOT NULL DEFAULT '0',
  `SumTime` bigint(20) unsigned NOT NULL DEFAULT '0',
  `SeedingAvg` int(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_torrents`
--

DROP TABLE IF EXISTS `users_torrents`;

CREATE TABLE `users_torrents` (
  `uid` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  `seedtime` bigint(20) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) NOT NULL DEFAULT '0',
  `uploaded` bigint(20) NOT NULL DEFAULT '0',
  `real_downloaded` bigint(20) NOT NULL DEFAULT '0',
  `real_uploaded` bigint(20) NOT NULL DEFAULT '0',
  `snatched` int(11) NOT NULL DEFAULT '0',
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fid`,`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_votes`
--

DROP TABLE IF EXISTS `users_votes`;

CREATE TABLE `users_votes` (
  `UserID` int(10) unsigned NOT NULL,
  `GroupID` int(10) NOT NULL,
  `Type` enum('Up','Down') DEFAULT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserID`,`GroupID`) USING BTREE,
  KEY `GroupID` (`GroupID`) USING BTREE,
  KEY `Type` (`Type`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE,
  KEY `Vote` (`Type`,`GroupID`,`UserID`) USING BTREE,
  CONSTRAINT `users_votes_ibfk_1` FOREIGN KEY (`GroupID`) REFERENCES `torrents_group` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `users_votes_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `users_warnings_forums`
--

DROP TABLE IF EXISTS `users_warnings_forums`;

CREATE TABLE `users_warnings_forums` (
  `UserID` int(10) unsigned NOT NULL,
  `Comment` text NOT NULL,
  PRIMARY KEY (`UserID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `wiki_aliases`
--

DROP TABLE IF EXISTS `wiki_aliases`;

CREATE TABLE `wiki_aliases` (
  `Alias` varchar(50) NOT NULL,
  `UserID` int(10) NOT NULL,
  `ArticleID` int(10) DEFAULT NULL,
  PRIMARY KEY (`Alias`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `wiki_articles`
--

DROP TABLE IF EXISTS `wiki_articles`;

CREATE TABLE `wiki_articles` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Lan_id` int(11) NOT NULL,
  `Revision` int(10) NOT NULL DEFAULT '1',
  `Title` varchar(100) DEFAULT NULL,
  `Body` mediumtext,
  `MinClassRead` int(4) DEFAULT NULL,
  `MinClassEdit` int(4) DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  `Author` int(10) DEFAULT NULL,
  `Father` int(11) NOT NULL,
  `Lang` varchar(15) NOT NULL DEFAULT 'chs',
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `wiki_artists`
--

DROP TABLE IF EXISTS `wiki_artists`;

CREATE TABLE `wiki_artists` (
  `RevisionID` int(12) NOT NULL AUTO_INCREMENT,
  `PageID` int(10) NOT NULL DEFAULT '0',
  `Body` text,
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Summary` varchar(100) DEFAULT NULL,
  `Time` datetime NOT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `IMDBID` varchar(15) DEFAULT NULL,
  `ChineseName` varchar(255) NOT NULL,
  `Birthday` varchar(255) NOT NULL,
  `PlaceOfBirth` varchar(255) NOT NULL,
  PRIMARY KEY (`RevisionID`) USING BTREE,
  KEY `PageID` (`PageID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `wiki_revisions`
--

DROP TABLE IF EXISTS `wiki_revisions`;

CREATE TABLE `wiki_revisions` (
  `ID` int(10) NOT NULL,
  `Revision` int(10) NOT NULL,
  `Title` varchar(100) DEFAULT NULL,
  `Body` mediumtext,
  `Date` datetime DEFAULT NULL,
  `Author` int(10) DEFAULT NULL,
  KEY `ID_Revision` (`ID`,`Revision`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `wiki_torrents`
--

DROP TABLE IF EXISTS `wiki_torrents`;

CREATE TABLE `wiki_torrents` (
  `RevisionID` int(12) NOT NULL AUTO_INCREMENT,
  `PageID` int(10) NOT NULL DEFAULT '0',
  `Body` text,
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Summary` varchar(100) DEFAULT NULL,
  `Time` datetime NOT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `IMDBRating` float DEFAULT NULL,
  `DoubanRating` float DEFAULT NULL,
  `Duration` smallint(2) DEFAULT NULL,
  `ReleaseDate` varchar(15) DEFAULT NULL,
  `Region` varchar(100) DEFAULT NULL,
  `Language` varchar(100) DEFAULT NULL,
  `IMDBID` varchar(255) NOT NULL,
  `RTRating` varchar(255) DEFAULT NULL,
  `DoubanID` int(11) DEFAULT NULL,
  `DoubanVote` int(11) DEFAULT NULL,
  `IMDBVote` int(11) DEFAULT NULL,
  `RTTitle` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`RevisionID`) USING BTREE,
  KEY `PageID` (`PageID`) USING BTREE,
  KEY `UserID` (`UserID`) USING BTREE,
  KEY `Time` (`Time`) USING BTREE,
  KEY `IMDBID` (`IMDBID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `xbt_client_whitelist`
--

DROP TABLE IF EXISTS `xbt_client_whitelist`;

CREATE TABLE `xbt_client_whitelist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `peer_id` varchar(20) DEFAULT NULL,
  `vstring` varchar(200) DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `peer_id` (`peer_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `xbt_files_history`
--

DROP TABLE IF EXISTS `xbt_files_history`;

CREATE TABLE `xbt_files_history` (
  `uid` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  `seedtime` int(11) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) NOT NULL DEFAULT '0',
  `uploaded` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `xbt_files_users`
--

DROP TABLE IF EXISTS `xbt_files_users`;

CREATE TABLE `xbt_files_users` (
  `uid` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `announced` int(11) NOT NULL DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) NOT NULL DEFAULT '0',
  `remaining` bigint(20) NOT NULL DEFAULT '0',
  `uploaded` bigint(20) NOT NULL DEFAULT '0',
  `upspeed` int(10) unsigned NOT NULL DEFAULT '0',
  `downspeed` int(10) unsigned NOT NULL DEFAULT '0',
  `corrupt` bigint(20) NOT NULL DEFAULT '0',
  `timespent` int(10) unsigned NOT NULL DEFAULT '0',
  `useragent` varchar(51) NOT NULL DEFAULT '',
  `connectable` tinyint(4) NOT NULL DEFAULT '1',
  `peer_id` binary(20) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `fid` int(11) NOT NULL,
  `mtime` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `ipv6` varchar(45) NOT NULL,
  PRIMARY KEY (`peer_id`,`fid`,`uid`) USING BTREE,
  KEY `remaining_idx` (`remaining`) USING BTREE,
  KEY `fid_idx` (`fid`) USING BTREE,
  KEY `mtime_idx` (`mtime`) USING BTREE,
  KEY `uid_active` (`uid`,`active`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;


--
-- Table structure for table `xbt_snatched`
--

DROP TABLE IF EXISTS `xbt_snatched`;

CREATE TABLE `xbt_snatched` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  `IP` varchar(15) NOT NULL,
  `seedtime` int(11) NOT NULL DEFAULT '0',
  `ipv6` varchar(45) NOT NULL,
  KEY `fid` (`fid`) USING BTREE,
  KEY `tstamp` (`tstamp`) USING BTREE,
  KEY `uid_tstamp` (`uid`,`tstamp`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;

-- ----------------------------
-- Function structure for binomial_ci
-- ----------------------------
DROP FUNCTION IF EXISTS `binomial_ci`;
CREATE DEFINER=`root`@`%` FUNCTION `binomial_ci`(p int, n int) RETURNS float
 DETERMINISTIC
 SQL SECURITY INVOKER
RETURN IF(n = 0,0.0,((p + 1.35336) / n - 1.6452 * SQRT((p * (n-p)) / n + 0.67668) / n) / (1 + 2.7067 / n));
-- ----------------------------
-- Function structure for size_correct
-- ----------------------------
DROP FUNCTION IF EXISTS `size_correct`;
DELIMITER //
CREATE DEFINER=`root`@`%` FUNCTION `size_correct`(`size` DOUBLE) RETURNS double
 NO SQL
BEGIN
declare res double default 0;
if size <= 1.6 then 
    set res = size;
else
    set res = 2.232158 - 3.947062 * pow (0.379185, size) + 0.127678 * size;
end if;
RETURN res;
END
//
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
----------------
-- data 
----------------
-- stylesheets
INSERT INTO `stylesheets` VALUES (35, 'GPW Dark Mono', 'Dark Mono', '1');

-- thread_type for applicant
INSERT INTO `thread_type` (`Name`) VALUES ('staff-role');

-- staff_groups
INSERT INTO `staff_groups` VALUES (1, 7, 'Moderators');
INSERT INTO `staff_groups` VALUES (2, 8, 'Human Resource');
INSERT INTO `staff_groups` VALUES (3, 9, 'Developers');
INSERT INTO `staff_groups` VALUES (4, 10, 'Administrators');
INSERT INTO `staff_groups` VALUES (5, 2, 'Official Staffs');
INSERT INTO `staff_groups` VALUES (6, 1, 'Secondary Class');

-- permissions
INSERT INTO `permissions` VALUES (2,100,'User','a:10:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_advanced_search\";i:1;s:20:\"site_torrents_notify\";i:1;s:16:\"site_album_votes\";i:1;s:14:\"site_edit_wiki\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:11:\"MaxCollages\";s:1:\"0\";}','0','',0,6);
INSERT INTO `permissions` VALUES (3,150,'Member','a:16:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:14:\"zip_downloader\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:11:\"MaxCollages\";s:1:\"0\";}','0','',0,6);
INSERT INTO `permissions` VALUES (4,200,'Power User','a:20:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:19:\"forums_polls_create\";i:1;s:14:\"zip_downloader\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:11:\"MaxCollages\";s:1:\"1\";}','0','',0,6);
INSERT INTO `permissions` VALUES (5,250,'Elite','a:26:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:19:\"forums_polls_create\";i:1;s:15:\"site_delete_tag\";i:1;s:14:\"zip_downloader\";i:1;s:13:\"torrents_edit\";i:1;s:19:\"self_torrents_check\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:18:\"torrents_trumpable\";i:1;s:11:\"MaxCollages\";s:1:\"2\";}','0','',0,6);
INSERT INTO `permissions` VALUES (11,900,'Moderator','a:80:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:20:\"site_collages_delete\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:27:\"site_send_unlimited_invites\";i:1;s:22:\"site_moderate_requests\";i:1;s:18:\"site_delete_artist\";i:1;s:19:\"forums_polls_create\";i:1;s:20:\"site_moderate_forums\";i:1;s:17:\"site_admin_forums\";i:1;s:28:\"site_view_torrent_snatchlist\";i:1;s:15:\"site_delete_tag\";i:1;s:23:\"site_disable_ip_history\";i:1;s:14:\"zip_downloader\";i:1;s:16:\"site_search_many\";i:1;s:12:\"project_team\";i:1;s:21:\"site_tag_aliases_read\";i:1;s:17:\"forums_see_hidden\";i:1;s:15:\"show_admin_team\";i:1;s:19:\"show_staff_username\";i:1;s:17:\"users_edit_titles\";i:1;s:18:\"users_edit_avatars\";i:1;s:18:\"users_edit_invites\";i:1;s:21:\"users_edit_reset_keys\";i:1;s:18:\"users_view_friends\";i:1;s:10:\"users_warn\";i:1;s:19:\"users_disable_users\";i:1;s:19:\"users_disable_posts\";i:1;s:17:\"users_disable_any\";i:1;s:18:\"users_view_invites\";i:1;s:20:\"users_view_seedleech\";i:1;s:19:\"users_view_uploaded\";i:1;s:15:\"users_view_keys\";i:1;s:14:\"users_view_ips\";i:1;s:16:\"users_view_email\";i:1;s:18:\"users_invite_notes\";i:1;s:23:\"users_override_paranoia\";i:1;s:12:\"users_logout\";i:1;s:9:\"users_mod\";i:1;s:11:\"staff_award\";i:1;s:19:\"users_view_disabled\";i:1;s:13:\"torrents_edit\";i:1;s:14:\"torrents_check\";i:1;s:18:\"torrents_check_log\";i:1;s:15:\"torrents_delete\";i:1;s:20:\"torrents_delete_fast\";i:1;s:18:\"torrents_freeleech\";i:1;s:20:\"torrents_search_fast\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:19:\"torrents_fix_ghosts\";i:1;s:18:\"torrents_trumpable\";i:1;s:18:\"torrents_slot_edit\";i:1;s:17:\"admin_manage_blog\";i:1;s:19:\"admin_manage_forums\";i:1;s:16:\"admin_manage_fls\";i:1;s:19:\"admin_manage_badges\";i:1;s:23:\"admin_manage_applicants\";i:1;s:16:\"admin_send_bonus\";i:1;s:13:\"admin_reports\";i:1;s:26:\"admin_advanced_user_search\";i:1;s:17:\"admin_clear_cache\";i:1;s:15:\"admin_whitelist\";i:1;s:17:\"admin_manage_wiki\";i:1;s:17:\"admin_interviewer\";i:1;s:11:\"MaxCollages\";s:1:\"6\";}','1','',0,1);
INSERT INTO `permissions` VALUES (15,1000,'Sysop','a:120:{s:10:"site_leech";i:1;s:11:"site_upload";i:1;s:9:"site_vote";i:1;s:20:"site_submit_requests";i:1;s:20:"site_advanced_search";i:1;s:10:"site_top10";i:1;s:20:"site_torrents_notify";i:1;s:20:"site_collages_create";i:1;s:20:"site_collages_manage";i:1;s:20:"site_collages_delete";i:1;s:23:"site_collages_subscribe";i:1;s:22:"site_collages_personal";i:1;s:28:"site_collages_renamepersonal";i:1;s:19:"site_advanced_top10";i:1;s:16:"site_album_votes";i:1;s:19:"site_make_bookmarks";i:1;s:14:"site_edit_wiki";i:1;s:22:"site_can_invite_always";i:1;s:15:"site_can_invite";i:1;s:27:"site_send_unlimited_invites";i:1;s:22:"site_moderate_requests";i:1;s:18:"site_delete_artist";i:1;s:19:"forums_polls_create";i:1;s:21:"forums_polls_moderate";i:1;s:20:"site_moderate_forums";i:1;s:17:"site_admin_forums";i:1;s:14:"site_view_flow";i:1;s:18:"site_view_full_log";i:1;s:28:"site_view_torrent_snatchlist";i:1;s:18:"site_recommend_own";i:1;s:27:"site_manage_recommendations";i:1;s:15:"site_delete_tag";i:1;s:23:"site_disable_ip_history";i:1;s:14:"zip_downloader";i:1;s:10:"site_debug";i:1;s:16:"site_search_many";i:1;s:21:"site_collages_recover";i:1;s:12:"project_team";i:1;s:21:"site_tag_aliases_read";i:1;s:17:"forums_see_hidden";i:1;s:15:"show_admin_team";i:1;s:19:"show_staff_username";i:1;s:20:"users_edit_usernames";i:1;s:16:"users_edit_ratio";i:1;s:20:"users_edit_own_ratio";i:1;s:17:"users_edit_titles";i:1;s:18:"users_edit_avatars";i:1;s:18:"users_edit_invites";i:1;s:22:"users_edit_watch_hours";i:1;s:21:"users_edit_reset_keys";i:1;s:19:"users_edit_profiles";i:1;s:18:"users_view_friends";i:1;s:20:"users_reset_own_keys";i:1;s:19:"users_edit_password";i:1;s:19:"users_promote_below";i:1;s:16:"users_promote_to";i:1;s:16:"users_give_donor";i:1;s:10:"users_warn";i:1;s:19:"users_disable_users";i:1;s:19:"users_disable_posts";i:1;s:17:"users_disable_any";i:1;s:18:"users_delete_users";i:1;s:18:"users_view_invites";i:1;s:20:"users_view_seedleech";i:1;s:19:"users_view_uploaded";i:1;s:15:"users_view_keys";i:1;s:14:"users_view_ips";i:1;s:16:"users_view_email";i:1;s:18:"users_invite_notes";i:1;s:23:"users_override_paranoia";i:1;s:20:"users_make_invisible";i:1;s:12:"users_logout";i:1;s:9:"users_mod";i:1;s:11:"staff_award";i:1;s:19:"users_view_disabled";i:1;s:13:"torrents_edit";i:1;s:14:"torrents_check";i:1;s:19:"self_torrents_check";i:1;s:18:"torrents_check_log";i:1;s:15:"torrents_delete";i:1;s:20:"torrents_delete_fast";i:1;s:18:"torrents_freeleech";i:1;s:20:"torrents_search_fast";i:1;s:19:"torrents_add_artist";i:1;s:13:"edit_unknowns";i:1;s:17:"torrents_hide_dnu";i:1;s:19:"torrents_fix_ghosts";i:1;s:18:"torrents_trumpable";i:1;s:18:"torrents_slot_edit";i:1;s:17:"admin_manage_news";i:1;s:17:"admin_manage_blog";i:1;s:18:"admin_manage_polls";i:1;s:19:"admin_manage_forums";i:1;s:16:"admin_manage_fls";i:1;s:21:"admin_manage_user_fls";i:1;s:19:"admin_manage_badges";i:1;s:23:"admin_manage_applicants";i:1;s:16:"admin_send_bonus";i:1;s:13:"admin_reports";i:1;s:16:"admin_bp_history";i:1;s:26:"admin_advanced_user_search";i:1;s:18:"admin_create_users";i:1;s:15:"admin_donor_log";i:1;s:24:"admin_manage_stylesheets";i:1;s:19:"admin_manage_ipbans";i:1;s:9:"admin_dnu";i:1;s:17:"admin_clear_cache";i:1;s:15:"admin_whitelist";i:1;s:24:"admin_manage_permissions";i:1;s:14:"admin_schedule";i:1;s:17:"admin_login_watch";i:1;s:17:"admin_manage_wiki";i:1;s:18:"admin_update_geoip";i:1;s:17:"admin_interviewer";i:1;s:20:"events_reward_tokens";i:1;s:19:"events_reward_bonus";i:1;s:21:"events_reward_invites";i:1;s:20:"events_reward_badges";i:1;s:21:"events_reward_history";i:1;s:11:"MaxCollages";s:0:"";}','1','',0,4);
INSERT INTO `permissions` VALUES (20,201,'Donor','a:18:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:15:\"site_can_invite\";i:1;s:19:\"forums_polls_create\";i:1;s:14:\"zip_downloader\";i:1;s:11:\"MaxCollages\";s:1:\"1\";}','0','',0,6);
INSERT INTO `permissions` VALUES (21,800,'Forum Moderator','a:55:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:27:\"site_send_unlimited_invites\";i:1;s:19:\"forums_polls_create\";i:1;s:21:\"forums_polls_moderate\";i:1;s:20:\"site_moderate_forums\";i:1;s:17:\"site_admin_forums\";i:1;s:28:\"site_view_torrent_snatchlist\";i:1;s:15:\"site_delete_tag\";i:1;s:23:\"site_disable_ip_history\";i:1;s:14:\"zip_downloader\";i:1;s:16:\"site_search_many\";i:1;s:12:\"project_team\";i:1;s:21:\"site_tag_aliases_read\";i:1;s:17:\"forums_see_hidden\";i:1;s:15:\"show_admin_team\";i:1;s:19:\"show_staff_username\";i:1;s:18:\"users_view_friends\";i:1;s:10:\"users_warn\";i:1;s:19:\"users_disable_posts\";i:1;s:20:\"users_view_seedleech\";i:1;s:19:\"users_view_uploaded\";i:1;s:18:\"users_invite_notes\";i:1;s:23:\"users_override_paranoia\";i:1;s:9:\"users_mod\";i:1;s:11:\"staff_award\";i:1;s:19:\"users_view_disabled\";i:1;s:13:\"torrents_edit\";i:1;s:14:\"torrents_check\";i:1;s:19:\"self_torrents_check\";i:1;s:18:\"torrents_freeleech\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:19:\"torrents_fix_ghosts\";i:1;s:18:\"torrents_trumpable\";i:1;s:16:\"admin_send_bonus\";i:1;s:13:\"admin_reports\";i:1;s:26:\"admin_advanced_user_search\";i:1;s:17:\"admin_manage_wiki\";i:1;s:11:\"MaxCollages\";s:1:\"5\";}','1','',0,1);
INSERT INTO `permissions` VALUES (22,850,'Torrent Moderator','a:64:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:27:\"site_send_unlimited_invites\";i:1;s:18:\"site_delete_artist\";i:1;s:19:\"forums_polls_create\";i:1;s:20:\"site_moderate_forums\";i:1;s:17:\"site_admin_forums\";i:1;s:28:\"site_view_torrent_snatchlist\";i:1;s:15:\"site_delete_tag\";i:1;s:23:\"site_disable_ip_history\";i:1;s:14:\"zip_downloader\";i:1;s:16:\"site_search_many\";i:1;s:12:\"project_team\";i:1;s:21:\"site_tag_aliases_read\";i:1;s:17:\"forums_see_hidden\";i:1;s:15:\"show_admin_team\";i:1;s:19:\"show_staff_username\";i:1;s:21:\"users_edit_reset_keys\";i:1;s:18:\"users_view_friends\";i:1;s:10:\"users_warn\";i:1;s:17:\"users_disable_any\";i:1;s:20:\"users_view_seedleech\";i:1;s:19:\"users_view_uploaded\";i:1;s:15:\"users_view_keys\";i:1;s:14:\"users_view_ips\";i:1;s:16:\"users_view_email\";i:1;s:18:\"users_invite_notes\";i:1;s:23:\"users_override_paranoia\";i:1;s:9:\"users_mod\";i:1;s:11:\"staff_award\";i:1;s:19:\"users_view_disabled\";i:1;s:13:\"torrents_edit\";i:1;s:14:\"torrents_check\";i:1;s:19:\"self_torrents_check\";i:1;s:18:\"torrents_check_log\";i:1;s:15:\"torrents_delete\";i:1;s:20:\"torrents_delete_fast\";i:1;s:18:\"torrents_freeleech\";i:1;s:20:\"torrents_search_fast\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:19:\"torrents_fix_ghosts\";i:1;s:18:\"torrents_trumpable\";i:1;s:18:\"torrents_slot_edit\";i:1;s:16:\"admin_send_bonus\";i:1;s:13:\"admin_reports\";i:1;s:26:\"admin_advanced_user_search\";i:1;s:17:\"admin_clear_cache\";i:1;s:11:\"MaxCollages\";s:1:\"6\";}','1','',0,1);
INSERT INTO `permissions` VALUES (23,255,'First Line Support','a:4:{s:22:\"site_collages_personal\";i:1;s:19:\"site_advanced_top10\";i:1;s:11:\"staff_award\";i:1;s:11:\"MaxCollages\";s:1:\"1\";}','1','28',1,6);
INSERT INTO `permissions` VALUES (24,950,'Developer','a:74:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:20:\"site_collages_delete\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:22:\"site_moderate_requests\";i:1;s:18:\"site_delete_artist\";i:1;s:19:\"forums_polls_create\";i:1;s:21:\"forums_polls_moderate\";i:1;s:17:\"site_admin_forums\";i:1;s:14:\"site_view_flow\";i:1;s:18:\"site_view_full_log\";i:1;s:28:\"site_view_torrent_snatchlist\";i:1;s:18:\"site_recommend_own\";i:1;s:27:\"site_manage_recommendations\";i:1;s:15:\"site_delete_tag\";i:1;s:23:\"site_disable_ip_history\";i:1;s:14:\"zip_downloader\";i:1;s:10:\"site_debug\";i:1;s:16:\"site_search_many\";i:1;s:21:\"site_collages_recover\";i:1;s:12:\"project_team\";i:1;s:21:\"site_tag_aliases_read\";i:1;s:17:\"forums_see_hidden\";i:1;s:15:\"show_admin_team\";i:1;s:19:\"show_staff_username\";i:1;s:16:\"users_give_donor\";i:1;s:19:\"users_view_uploaded\";i:1;s:14:\"users_view_ips\";i:1;s:18:\"users_invite_notes\";i:1;s:9:\"users_mod\";i:1;s:11:\"staff_award\";i:1;s:19:\"users_view_disabled\";i:1;s:13:\"torrents_edit\";i:1;s:18:\"torrents_check_log\";i:1;s:20:\"torrents_search_fast\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:17:\"admin_manage_blog\";i:1;s:18:\"admin_manage_polls\";i:1;s:19:\"admin_manage_forums\";i:1;s:21:\"admin_manage_user_fls\";i:1;s:13:\"admin_reports\";i:1;s:16:\"admin_bp_history\";i:1;s:26:\"admin_advanced_user_search\";i:1;s:15:\"admin_donor_log\";i:1;s:24:\"admin_manage_stylesheets\";i:1;s:19:\"admin_manage_ipbans\";i:1;s:17:\"admin_clear_cache\";i:1;s:15:\"admin_whitelist\";i:1;s:24:\"admin_manage_permissions\";i:1;s:14:\"admin_schedule\";i:1;s:17:\"admin_login_watch\";i:1;s:18:\"admin_update_geoip\";i:1;s:17:\"admin_interviewer\";i:1;s:20:\"events_reward_tokens\";i:1;s:19:\"events_reward_bonus\";i:1;s:21:\"events_reward_invites\";i:1;s:20:\"events_reward_badges\";i:1;s:21:\"events_reward_history\";i:1;s:11:\"MaxCollages\";s:1:\"1\";}','1','35',0,3);
INSERT INTO `permissions` VALUES (25,400,'Torrent Master','a:26:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:19:\"forums_polls_create\";i:1;s:15:\"site_delete_tag\";i:1;s:14:\"zip_downloader\";i:1;s:13:\"torrents_edit\";i:1;s:19:\"self_torrents_check\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:18:\"torrents_trumpable\";i:1;s:11:\"MaxCollages\";s:1:\"3\";}','0','',0,6);
INSERT INTO `permissions` VALUES (26,601,'VIP','a:24:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:19:\"forums_polls_create\";i:1;s:15:\"site_delete_tag\";i:1;s:14:\"zip_downloader\";i:1;s:13:\"torrents_edit\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:11:\"MaxCollages\";s:1:\"4\";}','0','',0,6);
INSERT INTO `permissions` VALUES (27,701,'Legend','a:35:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:27:\"site_send_unlimited_invites\";i:1;s:19:\"forums_polls_create\";i:1;s:15:\"site_delete_tag\";i:1;s:14:\"zip_downloader\";i:1;s:21:\"site_tag_aliases_read\";i:1;s:15:\"show_admin_team\";i:1;s:18:\"users_view_friends\";i:1;s:18:\"users_view_invites\";i:1;s:20:\"users_view_seedleech\";i:1;s:19:\"users_view_uploaded\";i:1;s:18:\"users_invite_notes\";i:1;s:23:\"users_override_paranoia\";i:1;s:11:\"staff_award\";i:1;s:13:\"torrents_edit\";i:1;s:14:\"torrents_check\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:11:\"MaxCollages\";s:1:\"6\";}','0','',0,6);
INSERT INTO `permissions` VALUES (28,501,'Guru','a:29:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:27:\"site_send_unlimited_invites\";i:1;s:19:\"forums_polls_create\";i:1;s:15:\"site_delete_tag\";i:1;s:14:\"zip_downloader\";i:1;s:13:\"torrents_edit\";i:1;s:14:\"torrents_check\";i:1;s:19:\"self_torrents_check\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:18:\"torrents_trumpable\";i:1;s:16:\"admin_manage_fls\";i:1;s:11:\"MaxCollages\";s:1:\"5\";}','0','',0,6);
INSERT INTO `permissions` VALUES (29,450,'Power TM','a:27:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:19:\"forums_polls_create\";i:1;s:15:\"site_delete_tag\";i:1;s:14:\"zip_downloader\";i:1;s:13:\"torrents_edit\";i:1;s:14:\"torrents_check\";i:1;s:19:\"self_torrents_check\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:18:\"torrents_trumpable\";i:1;s:11:\"MaxCollages\";s:1:\"4\";}','0','',0,6);
INSERT INTO `permissions` VALUES (30,300,'Interviewer','a:3:{s:11:\"staff_award\";i:1;s:17:\"admin_interviewer\";i:1;s:11:\"MaxCollages\";s:1:\"0\";}','0','30',1,6);
INSERT INTO `permissions` VALUES (31,210,'Torrent Celebrity','a:4:{s:17:\"forums_see_hidden\";i:1;s:16:\"users_view_email\";i:1;s:19:\"users_view_disabled\";i:1;s:11:\"MaxCollages\";s:1:\"0\";}','0','29',1,6);
INSERT INTO `permissions` VALUES (32,320,'Designer','a:14:{s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:19:\"forums_polls_create\";i:1;s:11:\"MaxCollages\";s:1:\"5\";}','1','33',1,3);
INSERT INTO `permissions` VALUES (40,980,'Administrator','a:120:{s:10:"site_leech";i:1;s:11:"site_upload";i:1;s:9:"site_vote";i:1;s:20:"site_submit_requests";i:1;s:20:"site_advanced_search";i:1;s:10:"site_top10";i:1;s:20:"site_torrents_notify";i:1;s:20:"site_collages_create";i:1;s:20:"site_collages_manage";i:1;s:20:"site_collages_delete";i:1;s:23:"site_collages_subscribe";i:1;s:22:"site_collages_personal";i:1;s:28:"site_collages_renamepersonal";i:1;s:19:"site_advanced_top10";i:1;s:16:"site_album_votes";i:1;s:19:"site_make_bookmarks";i:1;s:14:"site_edit_wiki";i:1;s:22:"site_can_invite_always";i:1;s:15:"site_can_invite";i:1;s:27:"site_send_unlimited_invites";i:1;s:22:"site_moderate_requests";i:1;s:18:"site_delete_artist";i:1;s:19:"forums_polls_create";i:1;s:21:"forums_polls_moderate";i:1;s:20:"site_moderate_forums";i:1;s:17:"site_admin_forums";i:1;s:14:"site_view_flow";i:1;s:18:"site_view_full_log";i:1;s:28:"site_view_torrent_snatchlist";i:1;s:18:"site_recommend_own";i:1;s:27:"site_manage_recommendations";i:1;s:15:"site_delete_tag";i:1;s:23:"site_disable_ip_history";i:1;s:14:"zip_downloader";i:1;s:10:"site_debug";i:1;s:16:"site_search_many";i:1;s:21:"site_collages_recover";i:1;s:12:"project_team";i:1;s:21:"site_tag_aliases_read";i:1;s:17:"forums_see_hidden";i:1;s:15:"show_admin_team";i:1;s:19:"show_staff_username";i:1;s:20:"users_edit_usernames";i:1;s:16:"users_edit_ratio";i:1;s:20:"users_edit_own_ratio";i:1;s:17:"users_edit_titles";i:1;s:18:"users_edit_avatars";i:1;s:18:"users_edit_invites";i:1;s:22:"users_edit_watch_hours";i:1;s:21:"users_edit_reset_keys";i:1;s:19:"users_edit_profiles";i:1;s:18:"users_view_friends";i:1;s:20:"users_reset_own_keys";i:1;s:19:"users_edit_password";i:1;s:19:"users_promote_below";i:1;s:16:"users_promote_to";i:1;s:16:"users_give_donor";i:1;s:10:"users_warn";i:1;s:19:"users_disable_users";i:1;s:19:"users_disable_posts";i:1;s:17:"users_disable_any";i:1;s:18:"users_delete_users";i:1;s:18:"users_view_invites";i:1;s:20:"users_view_seedleech";i:1;s:19:"users_view_uploaded";i:1;s:15:"users_view_keys";i:1;s:14:"users_view_ips";i:1;s:16:"users_view_email";i:1;s:18:"users_invite_notes";i:1;s:23:"users_override_paranoia";i:1;s:20:"users_make_invisible";i:1;s:12:"users_logout";i:1;s:9:"users_mod";i:1;s:11:"staff_award";i:1;s:19:"users_view_disabled";i:1;s:13:"torrents_edit";i:1;s:14:"torrents_check";i:1;s:19:"self_torrents_check";i:1;s:18:"torrents_check_log";i:1;s:15:"torrents_delete";i:1;s:20:"torrents_delete_fast";i:1;s:18:"torrents_freeleech";i:1;s:20:"torrents_search_fast";i:1;s:19:"torrents_add_artist";i:1;s:13:"edit_unknowns";i:1;s:17:"torrents_hide_dnu";i:1;s:19:"torrents_fix_ghosts";i:1;s:18:"torrents_trumpable";i:1;s:18:"torrents_slot_edit";i:1;s:17:"admin_manage_news";i:1;s:17:"admin_manage_blog";i:1;s:18:"admin_manage_polls";i:1;s:19:"admin_manage_forums";i:1;s:16:"admin_manage_fls";i:1;s:21:"admin_manage_user_fls";i:1;s:19:"admin_manage_badges";i:1;s:23:"admin_manage_applicants";i:1;s:16:"admin_send_bonus";i:1;s:13:"admin_reports";i:1;s:16:"admin_bp_history";i:1;s:26:"admin_advanced_user_search";i:1;s:18:"admin_create_users";i:1;s:15:"admin_donor_log";i:1;s:24:"admin_manage_stylesheets";i:1;s:19:"admin_manage_ipbans";i:1;s:9:"admin_dnu";i:1;s:17:"admin_clear_cache";i:1;s:15:"admin_whitelist";i:1;s:24:"admin_manage_permissions";i:1;s:14:"admin_schedule";i:1;s:17:"admin_login_watch";i:1;s:17:"admin_manage_wiki";i:1;s:18:"admin_update_geoip";i:1;s:17:"admin_interviewer";i:1;s:20:"events_reward_tokens";i:1;s:19:"events_reward_bonus";i:1;s:21:"events_reward_invites";i:1;s:20:"events_reward_badges";i:1;s:21:"events_reward_history";i:1;s:11:"MaxCollages";s:0:"";}','1','',0,4);
INSERT INTO `permissions` VALUES (42,205,'Donor','a:14:{s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:19:\"forums_polls_create\";i:1;s:14:\"zip_downloader\";i:1;s:11:\"MaxCollages\";i:1;}','0','10',1,NULL);
INSERT INTO `permissions` VALUES (44,500,'Elite TM','a:27:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:19:\"forums_polls_create\";i:1;s:15:\"site_delete_tag\";i:1;s:14:\"zip_downloader\";i:1;s:13:\"torrents_edit\";i:1;s:14:\"torrents_check\";i:1;s:19:\"self_torrents_check\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:18:\"torrents_trumpable\";i:1;s:11:\"MaxCollages\";s:1:\"5\";}','0','',0,6);
INSERT INTO `permissions` VALUES (45,410,'Pick Team','a:7:{s:15:\"site_delete_tag\";i:1;s:13:\"torrents_edit\";i:1;s:19:\"self_torrents_check\";i:1;s:18:\"torrents_freeleech\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:11:\"MaxCollages\";s:1:\"4\";}','0','',1,1);
INSERT INTO `permissions` VALUES (51,265,'Translators','a:3:{s:14:\"site_edit_wiki\";i:1;s:11:\"staff_award\";i:1;s:11:\"MaxCollages\";s:1:\"1\";}','1','58',1,6);
INSERT INTO `permissions` VALUES (52,350,'Sailing Team','a:1:{s:11:\"MaxCollages\";s:1:\"6\";}','0','',1,6);
INSERT INTO `permissions` VALUES (53,340,'Human Resource','a:1:{s:11:\"MaxCollages\";s:0:\"\";}','1','',1,5);
INSERT INTO `permissions` VALUES (56,420,'Torrent Inspector','a:16:{s:15:\"site_delete_tag\";i:1;s:16:\"site_search_many\";i:1;s:21:\"site_tag_aliases_read\";i:1;s:18:\"users_edit_avatars\";i:1;s:19:\"users_view_uploaded\";i:1;s:11:\"staff_award\";i:1;s:13:\"torrents_edit\";i:1;s:14:\"torrents_check\";i:1;s:19:\"self_torrents_check\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:18:\"torrents_trumpable\";i:1;s:18:\"torrents_slot_edit\";i:1;s:16:\"admin_send_bonus\";i:1;s:26:\"admin_advanced_user_search\";i:1;s:11:\"MaxCollages\";s:1:\"2\";}','1','58',1,6);
INSERT INTO `permissions` VALUES (57,311,'Official Inviter','a:5:{s:15:\"site_can_invite\";i:1;s:27:\"site_send_unlimited_invites\";i:1;s:18:\"users_view_invites\";i:1;s:18:\"users_invite_notes\";i:1;s:11:\"MaxCollages\";s:1:\"0\";}','0','',1,6);
INSERT INTO `permissions` VALUES (58,710,'Legends','a:28:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:19:\"forums_polls_create\";i:1;s:15:\"site_delete_tag\";i:1;s:14:\"zip_downloader\";i:1;s:18:\"users_view_friends\";i:1;s:23:\"users_override_paranoia\";i:1;s:11:\"staff_award\";i:1;s:13:\"torrents_edit\";i:1;s:19:\"self_torrents_check\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:11:\"MaxCollages\";s:1:\"6\";}','0','',1,6);
INSERT INTO `permissions` VALUES (60,502,'Super Elite TM','a:30:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:27:\"site_send_unlimited_invites\";i:1;s:19:\"forums_polls_create\";i:1;s:15:\"site_delete_tag\";i:1;s:14:\"zip_downloader\";i:1;s:13:\"torrents_edit\";i:1;s:14:\"torrents_check\";i:1;s:19:\"self_torrents_check\";i:1;s:18:\"torrents_check_log\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:18:\"torrents_trumpable\";i:1;s:16:\"admin_manage_fls\";i:1;s:11:\"MaxCollages\";s:0:\"\";}','0','',0,6);
INSERT INTO `permissions` VALUES (61,920,'Senior Moderator','a:95:{s:10:\"site_leech\";i:1;s:11:\"site_upload\";i:1;s:9:\"site_vote\";i:1;s:20:\"site_submit_requests\";i:1;s:20:\"site_advanced_search\";i:1;s:10:\"site_top10\";i:1;s:20:\"site_torrents_notify\";i:1;s:20:\"site_collages_create\";i:1;s:20:\"site_collages_manage\";i:1;s:20:\"site_collages_delete\";i:1;s:23:\"site_collages_subscribe\";i:1;s:22:\"site_collages_personal\";i:1;s:28:\"site_collages_renamepersonal\";i:1;s:19:\"site_advanced_top10\";i:1;s:16:\"site_album_votes\";i:1;s:19:\"site_make_bookmarks\";i:1;s:14:\"site_edit_wiki\";i:1;s:15:\"site_can_invite\";i:1;s:27:\"site_send_unlimited_invites\";i:1;s:22:\"site_moderate_requests\";i:1;s:18:\"site_delete_artist\";i:1;s:19:\"forums_polls_create\";i:1;s:21:\"forums_polls_moderate\";i:1;s:20:\"site_moderate_forums\";i:1;s:17:\"site_admin_forums\";i:1;s:14:\"site_view_flow\";i:1;s:18:\"site_view_full_log\";i:1;s:28:\"site_view_torrent_snatchlist\";i:1;s:15:\"site_delete_tag\";i:1;s:23:\"site_disable_ip_history\";i:1;s:14:\"zip_downloader\";i:1;s:16:\"site_search_many\";i:1;s:21:\"site_collages_recover\";i:1;s:12:\"project_team\";i:1;s:21:\"site_tag_aliases_read\";i:1;s:17:\"forums_see_hidden\";i:1;s:15:\"show_admin_team\";i:1;s:19:\"show_staff_username\";i:1;s:17:\"users_edit_titles\";i:1;s:18:\"users_edit_avatars\";i:1;s:18:\"users_edit_invites\";i:1;s:21:\"users_edit_reset_keys\";i:1;s:18:\"users_view_friends\";i:1;s:10:\"users_warn\";i:1;s:19:\"users_disable_users\";i:1;s:19:\"users_disable_posts\";i:1;s:17:\"users_disable_any\";i:1;s:18:\"users_view_invites\";i:1;s:20:\"users_view_seedleech\";i:1;s:19:\"users_view_uploaded\";i:1;s:15:\"users_view_keys\";i:1;s:14:\"users_view_ips\";i:1;s:16:\"users_view_email\";i:1;s:18:\"users_invite_notes\";i:1;s:23:\"users_override_paranoia\";i:1;s:12:\"users_logout\";i:1;s:9:\"users_mod\";i:1;s:11:\"staff_award\";i:1;s:19:\"users_view_disabled\";i:1;s:13:\"torrents_edit\";i:1;s:14:\"torrents_check\";i:1;s:19:\"self_torrents_check\";i:1;s:18:\"torrents_check_log\";i:1;s:15:\"torrents_delete\";i:1;s:20:\"torrents_delete_fast\";i:1;s:18:\"torrents_freeleech\";i:1;s:20:\"torrents_search_fast\";i:1;s:19:\"torrents_add_artist\";i:1;s:13:\"edit_unknowns\";i:1;s:19:\"torrents_fix_ghosts\";i:1;s:18:\"torrents_trumpable\";i:1;s:18:\"torrents_slot_edit\";i:1;s:17:\"admin_manage_blog\";i:1;s:18:\"admin_manage_polls\";i:1;s:19:\"admin_manage_forums\";i:1;s:16:\"admin_manage_fls\";i:1;s:21:\"admin_manage_user_fls\";i:1;s:19:\"admin_manage_badges\";i:1;s:23:\"admin_manage_applicants\";i:1;s:16:\"admin_send_bonus\";i:1;s:13:\"admin_reports\";i:1;s:16:\"admin_bp_history\";i:1;s:26:\"admin_advanced_user_search\";i:1;s:24:\"admin_manage_stylesheets\";i:1;s:9:\"admin_dnu\";i:1;s:17:\"admin_clear_cache\";i:1;s:15:\"admin_whitelist\";i:1;s:17:\"admin_manage_wiki\";i:1;s:17:\"admin_interviewer\";i:1;s:20:\"events_reward_tokens\";i:1;s:19:\"events_reward_bonus\";i:1;s:21:\"events_reward_invites\";i:1;s:20:\"events_reward_badges\";i:1;s:21:\"events_reward_history\";i:1;s:11:\"MaxCollages\";s:1:\"6\";}','1','',0,1);
INSERT INTO `permissions` VALUES (62,280,'Advisers','a:3:{s:19:\"self_torrents_check\";i:1;s:19:\"torrents_add_artist\";i:1;s:11:\"MaxCollages\";s:0:\"\";}','0','',1,6);