/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : admin-ui

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2018-03-01 18:08:45
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `admin`
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL COMMENT '所属权限组',
  `account` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', '1', 'admin', '7c6c9647c39b4c9d9504281231a01568', '2014-09-18 04:16:56');
INSERT INTO `admin` VALUES ('6', '2', 'test', 'ca23a2d45c4362a23bb957c3054fa496', '2018-02-24 17:11:56');

-- ----------------------------
-- Table structure for `admin_role`
-- ----------------------------
DROP TABLE IF EXISTS `admin_role`;
CREATE TABLE `admin_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `privilege` varchar(255) DEFAULT NULL COMMENT '拥有权限的菜单id，用逗号分隔',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_role
-- ----------------------------
INSERT INTO `admin_role` VALUES ('1', '超级管理员', '1,70,71,81,6,11,12,14,15,80', '2014-07-04 10:27:40');
INSERT INTO `admin_role` VALUES ('2', '普通员工12', '1,70,71,6,11,12,72,73', '2014-07-04 03:27:31');
INSERT INTO `admin_role` VALUES ('4', '销售专员', '11,12,14,15,69', null);

-- ----------------------------
-- Table structure for `info`
-- ----------------------------
DROP TABLE IF EXISTS `info`;
CREATE TABLE `info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` int(11) DEFAULT NULL COMMENT '关联分类id',
  `title` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `content` text,
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='基本信息表，包含公告，关于我们，公司简介等信息';

-- ----------------------------
-- Records of info
-- ----------------------------
INSERT INTO `info` VALUES ('1', '1', '关于我们', null, '关于我们关于我们关于我们', '2016-09-22 08:28:48');
INSERT INTO `info` VALUES ('2', '1', '联系我们', null, '联系我们联系我们', '2017-05-11 14:30:04');
INSERT INTO `info` VALUES ('3', '1', '隐私条款', null, '隐私条款', '2017-05-11 14:30:35');
INSERT INTO `info` VALUES ('4', '2', '公告公告公告', null, '公告公告公告公告公告公告', '2017-05-11 14:47:21');
INSERT INTO `info` VALUES ('5', '2', '公告公告公告', null, '公告公告公告公告公告公告', '2017-05-11 14:47:27');

-- ----------------------------
-- Table structure for `info_cate`
-- ----------------------------
DROP TABLE IF EXISTS `info_cate`;
CREATE TABLE `info_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of info_cate
-- ----------------------------
INSERT INTO `info_cate` VALUES ('1', '基本信息', '2016-09-22 08:41:31');
INSERT INTO `info_cate` VALUES ('2', '公告信息', '2017-05-11 14:22:18');

-- ----------------------------
-- Table structure for `menu`
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(6) unsigned NOT NULL DEFAULT '0',
  `icon` varchar(20) DEFAULT '' COMMENT '菜单图标',
  `name` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(50) DEFAULT '',
  `privilege` varchar(255) DEFAULT NULL COMMENT '权限',
  `is_menu` tinyint(4) DEFAULT '1' COMMENT '是否菜单显示',
  `sort` int(2) unsigned DEFAULT '0',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parentid` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=82 DEFAULT CHARSET=utf8 COMMENT='后台管理和会员管理 栏目表';

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES ('1', '0', '&#xe6df', '常用管理', '', '', '1', '0', '2016-08-31 07:49:52');
INSERT INTO `menu` VALUES ('6', '0', '&#xe758', '系统管理', '', '', '1', '1', '2016-08-31 07:50:06');
INSERT INTO `menu` VALUES ('55', '20', '&#xe621;', '电影管理', '/movie', '*', '1', '1', '2016-12-02 09:51:22');
INSERT INTO `menu` VALUES ('11', '6', '', '栏目管理', '', '', '1', '0', '2016-09-01 00:32:55');
INSERT INTO `menu` VALUES ('12', '11', '', '菜单与权限', '/system/menu/', '*', '1', '0', '2016-09-01 00:33:22');
INSERT INTO `menu` VALUES ('15', '14', '', '帐号管理', '/system/account/', '*', '1', '0', '2016-09-01 00:42:49');
INSERT INTO `menu` VALUES ('14', '6', '', '帐号管理', '', '', '1', '0', '2016-09-01 00:42:49');
INSERT INTO `menu` VALUES ('70', '1', '', '信息管理', '', '', '1', '0', '2017-11-02 17:03:45');
INSERT INTO `menu` VALUES ('71', '70', '', '信息列表', '/info/', '*', '1', '0', '2017-11-02 17:23:57');
INSERT INTO `menu` VALUES ('74', '20', '&#xe621;', '站点管理', '/site/btbtdy', '*', '1', '0', '2017-12-18 10:29:15');
INSERT INTO `menu` VALUES ('80', '14', '', '角色管理', '/system/role/', '*', '1', '0', '2018-02-23 16:53:48');
INSERT INTO `menu` VALUES ('81', '70', '', '信息分类', '/info_cate/', '*', '1', '0', '2018-03-01 17:47:06');
