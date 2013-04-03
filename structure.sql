

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP TABLE IF EXISTS `language`;

CREATE TABLE `language` (
  `id` varchar(3) NOT NULL,
  `name` varchar(48) DEFAULT NULL,
  `timezone` varchar(48) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*Data for the table `language` */

insert  into `language`(`id`,`name`,`timezone`) values ('en','English','Europe/London');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varbinary(100) DEFAULT NULL,
  `pass` varbinary(100) DEFAULT NULL,
  `username` varbinary(100) DEFAULT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `salt` varchar(12) DEFAULT NULL,
  `role` enum('0','1','2','4') DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_idx` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `users` */

insert  into `users`(`id`,`name`,`pass`,`username`,`surname`,`salt`,`role`) values (1,'Guest','1eea42010865310f6f42a000b1225658098ef935','guest',NULL,'958Om0MoGgt','0'),(3,'Administrator','3e4e2b155727719b28f6c6e0d335cca003665adb','admin',NULL,'4JTMCHKM1t9','4'),(5,'Authenticated User','52557ca0ca35c801ce80a5be7baf954ff41e31aa','auth',NULL,'reLJeHOKNME','0');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
