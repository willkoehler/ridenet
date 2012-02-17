SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity`;

CREATE TABLE `activity` (
  `ActivityID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Date` datetime DEFAULT NULL,
  `LoginTableID` int(10) unsigned DEFAULT NULL,
  `SiteID` smallint(5) unsigned DEFAULT NULL,
  `Description` varchar(150) DEFAULT NULL,
  `ReferenceID` int(10) unsigned DEFAULT NULL,
  `IPAddress` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ActivityID`),
  KEY `Date` (`Date`),
  KEY `LoginTableID` (`LoginTableID`)
) ENGINE=MyISAM AUTO_INCREMENT=55111 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `calendar`;

CREATE TABLE `calendar` (
  `CalendarID` int(11) NOT NULL AUTO_INCREMENT,
  `TeamID` smallint(5) unsigned DEFAULT NULL,
  `CalendarDate` datetime DEFAULT NULL,
  `EventName` varchar(100) DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `Directions` text,
  `IntensityID` int(11) DEFAULT NULL,
  `Comments` text,
  `ZipCodeID` int(5) unsigned DEFAULT NULL,
  `MapURL` varchar(255) DEFAULT NULL,
  `ClassX` tinyint(3) unsigned DEFAULT NULL,
  `ClassA` tinyint(3) unsigned DEFAULT NULL,
  `ClassB` tinyint(3) unsigned DEFAULT NULL,
  `ClassC` tinyint(3) unsigned DEFAULT NULL,
  `ClassD` tinyint(3) unsigned DEFAULT NULL,
  `AddedBy` int(11) DEFAULT NULL,
  `Archived` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`CalendarID`),
  KEY `Date` (`CalendarDate`)
) ENGINE=MyISAM AUTO_INCREMENT=844 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `calendar_attendance`;

CREATE TABLE `calendar_attendance` (
  `AttendanceID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CalendarID` int(10) unsigned NOT NULL,
  `RiderID` int(10) unsigned NOT NULL,
  `Attending` tinyint(3) unsigned NOT NULL,
  `Notify` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`AttendanceID`),
  KEY `CalendarId` (`CalendarID`),
  KEY `RiderID` (`RiderID`)
) ENGINE=MyISAM AUTO_INCREMENT=590 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `event`;

CREATE TABLE `event` (
  `RaceID` int(11) NOT NULL AUTO_INCREMENT,
  `RaceDate` date DEFAULT NULL,
  `EventName` varchar(100) DEFAULT NULL,
  `WebPage` varchar(255) DEFAULT NULL,
  `RideTypeID` int(11) DEFAULT NULL,
  `City` varchar(50) DEFAULT NULL,
  `StateID` tinyint(3) unsigned DEFAULT NULL,
  `DateAdded` date DEFAULT NULL,
  `AddedBy` int(11) DEFAULT NULL,
  `Archived` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`RaceID`),
  KEY `RaceID` (`RaceID`),
  KEY `RideType` (`RideTypeID`),
  KEY `RaceDate` (`RaceDate`)
) ENGINE=MyISAM AUTO_INCREMENT=2080 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `event_attendance`;

CREATE TABLE `event_attendance` (
  `AttendanceID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RaceID` int(10) unsigned NOT NULL,
  `RiderID` int(10) unsigned NOT NULL,
  `Attending` tinyint(3) unsigned NOT NULL,
  `Notify` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`AttendanceID`),
  KEY `RaceId` (`RaceID`),
  KEY `RiderID` (`RiderID`)
) ENGINE=MyISAM AUTO_INCREMENT=920 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `event_photos`;

CREATE TABLE `event_photos` (
  `PhotoID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RaceID` int(10) unsigned DEFAULT NULL,
  `RiderID` int(10) unsigned DEFAULT NULL,
  `TeamID` smallint(5) unsigned DEFAULT NULL,
  `Filename` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`PhotoID`),
  KEY `RiderID` (`RiderID`),
  KEY `RaceID&RiderID` (`RaceID`,`RiderID`)
) ENGINE=MyISAM AUTO_INCREMENT=197 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `following`;

CREATE TABLE `following` (
  `FollowerID` int(10) unsigned NOT NULL,
  `FollowingID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`FollowerID`,`FollowingID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `logins`;

CREATE TABLE `logins` (
  `LoginID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `RiderID` int(10) unsigned DEFAULT NULL,
  `LoginDate` datetime DEFAULT NULL,
  `IPAddress` varchar(50) DEFAULT NULL,
  `HTTP_USER_AGENT` varchar(200) DEFAULT NULL,
  `Browser` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`LoginID`)
) ENGINE=MyISAM AUTO_INCREMENT=16557 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `posts`;

CREATE TABLE `posts` (
  `PostID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RiderID` int(10) unsigned DEFAULT NULL,
  `TeamID` smallint(5) unsigned DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  `PostType` tinyint(3) unsigned DEFAULT NULL,
  `Text` varchar(255) DEFAULT NULL,
  `PostedToID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`PostID`),
  KEY `Date` (`Date`)
) ENGINE=MyISAM AUTO_INCREMENT=615 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `race_report`;

CREATE TABLE `race_report` (
  `RaceID` int(11) NOT NULL,
  `RiderID` int(11) NOT NULL,
  `TeamID` smallint(5) unsigned DEFAULT NULL,
  `Report` text,
  `DateFiled` date DEFAULT NULL,
  PRIMARY KEY (`RaceID`,`RiderID`),
  KEY `FiledBy` (`RiderID`),
  KEY `RaceID` (`RaceID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ref_event_type`;

CREATE TABLE `ref_event_type` (
  `RideTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `RideType` varchar(50) DEFAULT NULL,
  `Picture` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`RideTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ref_intensity`;

CREATE TABLE `ref_intensity` (
  `IntensityID` tinyint(3) unsigned NOT NULL,
  `Intensity` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`IntensityID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `ref_placing`;

CREATE TABLE `ref_placing` (
  `PlaceID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `PlaceName` varchar(50) DEFAULT NULL,
  `PlaceOrdinal` tinyint(3) unsigned DEFAULT NULL,
  `Points` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`PlaceID`),
  KEY `PlaceId` (`PlaceID`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ref_race_category`;

CREATE TABLE `ref_race_category` (
  `CategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(50) DEFAULT NULL,
  `Sort` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`CategoryID`),
  KEY `CategoryId` (`CategoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ref_ride_log_type`;

CREATE TABLE `ref_ride_log_type` (
  `RideLogTypeID` tinyint(3) unsigned NOT NULL,
  `RideLogType` varchar(50) DEFAULT NULL,
  `RideLogTypeImage` varchar(50) DEFAULT NULL,
  `RideLogDescription` varchar(100) DEFAULT NULL,
  `Sort` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`RideLogTypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `ref_rider_type`;

CREATE TABLE `ref_rider_type` (
  `RiderTypeID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `RiderType` varchar(50) NOT NULL,
  `Sort` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`RiderTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `ref_site_level`;

CREATE TABLE `ref_site_level` (
  `SiteLevelID` tinyint(3) unsigned NOT NULL,
  `SiteLevel` varchar(50) NOT NULL,
  PRIMARY KEY (`SiteLevelID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `ref_states`;

CREATE TABLE `ref_states` (
  `StateID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `StateAbbr` varchar(50) DEFAULT NULL,
  `StateName` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`StateID`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `ref_team_board_type`;

CREATE TABLE `ref_team_board_type` (
  `TeamBoardTypeID` tinyint(3) unsigned NOT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Image` varchar(50) DEFAULT NULL,
  `Sort` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`TeamBoardTypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `ref_team_type`;

CREATE TABLE `ref_team_type` (
  `TeamTypeID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `TeamType` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`TeamTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `ref_weather`;

CREATE TABLE `ref_weather` (
  `WeatherID` tinyint(3) unsigned NOT NULL,
  `Weather` varchar(50) DEFAULT NULL,
  `WeatherImage` varchar(50) DEFAULT NULL,
  `WeatherAbbr` varchar(10) DEFAULT NULL,
  `Sort` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`WeatherID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `ref_zipcodes`;

CREATE TABLE `ref_zipcodes` (
  `ZipCodeID` int(10) unsigned NOT NULL,
  `ZipCode` char(5) NOT NULL,
  `City` varchar(64) DEFAULT NULL,
  `State` char(2) DEFAULT NULL,
  `Latitude` double DEFAULT NULL,
  `Longitude` double DEFAULT NULL,
  `TimeZone` tinyint(4) DEFAULT NULL,
  `DST` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`ZipCodeID`),
  KEY `City` (`City`),
  KEY `State` (`State`),
  KEY `ZipCode` (`ZipCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `results`;

CREATE TABLE `results` (
  `RaceID` int(11) NOT NULL,
  `RiderID` int(11) NOT NULL,
  `CategoryID` int(11) NOT NULL,
  `TeamID` smallint(5) unsigned NOT NULL,
  `PlaceID` int(11) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  PRIMARY KEY (`RaceID`,`RiderID`,`CategoryID`),
  KEY `PlaceId` (`PlaceID`),
  KEY `RaceID` (`RaceID`),
  KEY `RiderNo` (`RiderID`),
  KEY `Created` (`Created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ride_log`;

CREATE TABLE `ride_log` (
  `RideLogID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RiderID` int(10) unsigned NOT NULL,
  `Date` date DEFAULT NULL,
  `RideLogTypeID` tinyint(3) unsigned DEFAULT NULL,
  `Distance` smallint(5) unsigned DEFAULT NULL,
  `Duration` smallint(5) unsigned DEFAULT NULL,
  `WeatherID` tinyint(3) unsigned DEFAULT NULL,
  `Comment` varchar(140) DEFAULT NULL,
  `Link` varchar(255) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Source` tinyint(3) unsigned DEFAULT NULL,
  `HasMap` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`RideLogID`),
  KEY `RiderID` (`RiderID`),
  KEY `Date+Created` (`Date`,`Created`),
  KEY `RiderID+Date` (`RiderID`,`Date`)
) ENGINE=MyISAM AUTO_INCREMENT=31945 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `ride_log_map`;

CREATE TABLE `ride_log_map` (
  `RideLogID` int(10) unsigned NOT NULL,
  `DateTime` datetime NOT NULL,
  `Latitude` int(11) NOT NULL,
  `Longitude` int(11) NOT NULL,
  `Altitude` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`RideLogID`,`DateTime`),
  KEY `RideLogID` (`RideLogID`),
  KEY `DateTime` (`DateTime`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `rider`;

CREATE TABLE `rider` (
  `RiderID` int(11) NOT NULL AUTO_INCREMENT,
  `RacingTeamID` smallint(5) unsigned DEFAULT NULL,
  `CommutingTeamID` smallint(5) unsigned DEFAULT NULL,
  `RiderTypeID` tinyint(3) unsigned DEFAULT NULL,
  `FirstName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `RiderEmail` varchar(50) DEFAULT NULL,
  `Archived` tinyint(4) DEFAULT NULL,
  `HideRaceReports` tinyint(3) unsigned DEFAULT NULL,
  `BornIn` varchar(60) DEFAULT NULL,
  `ResideIn` varchar(60) DEFAULT NULL,
  `YearsCycling` smallint(6) DEFAULT NULL,
  `Height` varchar(10) DEFAULT NULL,
  `Weight` smallint(5) unsigned DEFAULT NULL,
  `FavoriteFood` varchar(100) DEFAULT NULL,
  `URL` varchar(100) DEFAULT NULL,
  `MaritalStatus` varchar(60) DEFAULT NULL,
  `FavoriteRide` text,
  `FavoriteQuote` text,
  `Occupation` varchar(60) DEFAULT NULL,
  `WhyIRide` text,
  `MyCommute` text,
  `Password` char(63) DEFAULT NULL,
  `sRacingTeamAdmin` tinyint(3) unsigned DEFAULT NULL,
  `sCommutingTeamAdmin` tinyint(3) unsigned DEFAULT NULL,
  `sSystemAdmin` tinyint(3) unsigned DEFAULT NULL,
  `sDesigner` tinyint(3) unsigned DEFAULT NULL,
  `MapPrivacy` tinyint(3) unsigned DEFAULT NULL,
  `MustChangePW` tinyint(3) unsigned DEFAULT NULL,
  `DateCreated` date DEFAULT NULL,
  `CreatedByID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`RiderID`)
) ENGINE=MyISAM AUTO_INCREMENT=1723 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `rider_photos`;

CREATE TABLE `rider_photos` (
  `PhotoID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RiderID` int(10) unsigned NOT NULL,
  `TeamID` smallint(5) unsigned NOT NULL,
  `Picture` blob,
  `ActionPicture` mediumblob,
  `LastModified` datetime DEFAULT NULL,
  PRIMARY KEY (`PhotoID`),
  KEY `RiderID&TeamID` (`RiderID`,`TeamID`)
) ENGINE=MyISAM AUTO_INCREMENT=713 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `rider_stats`;

CREATE TABLE `rider_stats` (
  `RiderID` int(10) unsigned NOT NULL,
  `CEDaysMonth` tinyint(3) unsigned DEFAULT NULL,
  `CMilesDay` smallint(5) unsigned DEFAULT NULL,
  `A_Miles` smallint(5) unsigned DEFAULT NULL,
  `A_CEMiles` smallint(5) unsigned DEFAULT NULL,
  `A_Days` smallint(5) unsigned DEFAULT NULL,
  `A_CEDays` smallint(5) unsigned DEFAULT NULL,
  `A_CERides` smallint(5) unsigned DEFAULT NULL,
  `Y0_Miles` smallint(5) unsigned DEFAULT NULL,
  `Y0_CEMiles` smallint(5) unsigned DEFAULT NULL,
  `Y0_Days` smallint(5) unsigned DEFAULT NULL,
  `Y0_CEDays` smallint(5) unsigned DEFAULT NULL,
  `Y0_CERides` smallint(5) unsigned DEFAULT NULL,
  `Y1_Miles` smallint(5) unsigned DEFAULT NULL,
  `Y1_CEMiles` smallint(5) unsigned DEFAULT NULL,
  `Y1_Days` smallint(5) unsigned DEFAULT NULL,
  `Y1_CEDays` smallint(5) unsigned DEFAULT NULL,
  `Y1_CERides` smallint(5) unsigned DEFAULT NULL,
  `M0_Miles` smallint(5) unsigned DEFAULT NULL,
  `M0_CEMiles` smallint(5) unsigned DEFAULT NULL,
  `M0_Days` smallint(5) unsigned DEFAULT NULL,
  `M0_CEDays` smallint(5) unsigned DEFAULT NULL,
  `M0_CERides` smallint(5) unsigned DEFAULT NULL,
  `M1_Miles` smallint(5) unsigned DEFAULT NULL,
  `M1_CEMiles` smallint(5) unsigned DEFAULT NULL,
  `M1_Days` smallint(5) unsigned DEFAULT NULL,
  `M1_CEDays` smallint(5) unsigned DEFAULT NULL,
  `M1_CERides` smallint(5) unsigned DEFAULT NULL,
  `M2_Miles` smallint(5) unsigned DEFAULT NULL,
  `M2_CEMiles` smallint(5) unsigned DEFAULT NULL,
  `M2_Days` smallint(5) unsigned DEFAULT NULL,
  `M2_CEDays` smallint(5) unsigned DEFAULT NULL,
  `M2_CERides` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`RiderID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `rider_view_log`;

CREATE TABLE `rider_view_log` (
  `RiderViewID` int(11) NOT NULL AUTO_INCREMENT,
  `RiderID` int(11) DEFAULT NULL,
  `DateViewed` datetime DEFAULT NULL,
  PRIMARY KEY (`RiderViewID`),
  KEY `RiderViewId` (`RiderViewID`)
) ENGINE=MyISAM AUTO_INCREMENT=73459 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `system_config`;

CREATE TABLE `system_config` (
  `SMTPFromEmail` varchar(50) DEFAULT NULL,
  `SMTPServer` varchar(50) DEFAULT NULL,
  `SMTPPort` smallint(5) unsigned DEFAULT NULL,
  `SMTPAuthenticate` tinyint(3) unsigned DEFAULT NULL,
  `SMTPUsername` varchar(50) DEFAULT NULL,
  `SMTPPassword` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `team_images`;

CREATE TABLE `team_images` (
  `PhotoID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TeamID` smallint(5) unsigned NOT NULL,
  `Logo` blob,
  `Banner` mediumblob,
  `BodyBG` mediumblob,
  `HomePageImage` mediumblob,
  `LastModified` datetime DEFAULT NULL,
  PRIMARY KEY (`PhotoID`),
  KEY `TeamID` (`TeamID`)
) ENGINE=MyISAM AUTO_INCREMENT=154 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;


DROP TABLE IF EXISTS `teams`;

CREATE TABLE `teams` (
  `TeamID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Archived` tinyint(3) unsigned DEFAULT NULL,
  `bRacing` tinyint(3) unsigned DEFAULT NULL,
  `bCommuting` tinyint(3) unsigned DEFAULT NULL,
  `TeamTypeID` tinyint(3) unsigned DEFAULT NULL,
  `SiteLevelID` tinyint(3) unsigned DEFAULT NULL,
  `TeamName` varchar(75) DEFAULT NULL,
  `Domain` varchar(50) DEFAULT NULL,
  `ZipCodeID` int(10) unsigned DEFAULT NULL,
  `ShowLogo` tinyint(3) unsigned DEFAULT NULL,
  `PrivateCalendar` tinyint(3) unsigned DEFAULT NULL,
  `PrimaryColor` char(6) DEFAULT NULL,
  `SecondaryColor` char(6) DEFAULT NULL,
  `PageBGColor` char(6) DEFAULT NULL,
  `BodyBGColor` char(50) DEFAULT NULL,
  `LinkColor` char(6) DEFAULT NULL,
  `HomePageType` tinyint(3) unsigned DEFAULT NULL,
  `HomePageMoreWrap` tinyint(3) unsigned DEFAULT NULL,
  `HomePageHTML` text,
  `HomePageText` text,
  `HomePageTitle` text,
  PRIMARY KEY (`TeamID`)
) ENGINE=MyISAM AUTO_INCREMENT=163 DEFAULT CHARSET=latin1;


SET FOREIGN_KEY_CHECKS = 1;

DROP FUNCTION IF EXISTS `CalculateDistance`;
delimiter ;;
CREATE DEFINER=`admin`@`%` FUNCTION `CalculateDistance`(Longitude1 DOUBLE,  Latitude1 DOUBLE, Longitude2 DOUBLE, Latitude2 DOUBLE) RETURNS double
BEGIN
# Calculate distance between two points given longitude and latitude
	IF(Latitude1=Latitude2 AND Longitude1=Longitude2) THEN
		RETURN(0);
	ELSE
		RETURN(((ACOS(SIN(Latitude1 * PI() / 180) * SIN(Latitude2 * PI() / 180) + COS(Latitude1 * PI() / 180) * COS(Latitude2 * PI() / 180) * COS((Longitude1 - Longitude2) * PI() / 180)) * 180 / PI()) * 60 * 1.1515));
	END IF;
END;
 ;;
delimiter ;

