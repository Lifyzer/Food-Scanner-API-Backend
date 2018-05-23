--
-- Copyright:     (c) 2018, Pierre-Henry Soria. All Rights Reserved.
-- License:       GNU General Public License <https://www.gnu.org/licenses/gpl-3.0.en.html>
--

CREATE TABLE admin_config (
  id int(11) NOT NULL,
  config_key varchar(40) NOT NULL,
  config_value varchar(200) NOT NULL,
  value_unit varchar(20) NOT NULL,
  scope enum('All','Admin') NOT NULL DEFAULT 'All' COMMENT 'This will define value is applicable for which scope',
  created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  modified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  is_delete tinyint(1) NOT NULL DEFAULT '0',
  is_testdata varchar(3) NOT NULL DEFAULT 'no' COMMENT 'dev means non garbaged dummy data',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO admin_config (id, config_key, config_value, value_unit, scope, created, modified, is_delete, is_testdata) VALUES
(1, 'globalPassword', '_$(Skill)!_square@#$%_23_06_2017', 'text', 'All', '2016-09-26 12:21:02', '2017-06-23 11:41:40', 0, 'yes'),
(2, 'userAgent', 'iOS,Android,Mozilla/5.0,PostmanRuntime/2.5.0', 'comma-separated', 'All', '2016-06-28 13:22:22', '2016-09-26 12:46:41', 0, 'yes'),
(3, 'tempToken', 'allowAccessToApp', 'text', 'All', '2016-06-28 13:22:22', '0000-00-00 00:00:00', 0, 'yes'),
(4, 'expiry_duration', '3600', 'second', 'All', '2016-07-05 04:31:34', '0000-00-00 00:00:00', 0, 'yes'),
(5, 'autologout', '1', 'boolean', 'All', '2016-07-05 04:31:34', '0000-00-00 00:00:00', 0, 'yes'),
(6, 'appVersion', '1.0', 'text', 'All', '0000-00-00 00:00:00', '2017-05-11 09:11:55', 0, 'yes'),
(7, 'isUpdateOptional', '1', 'boolean', 'All', '0000-00-00 00:00:00', '2017-05-11 09:32:27', 0, 'yes');


CREATE TABLE app_tokens (
  id int(15) UNSIGNED NOT NULL,
  userid int(11) DEFAULT NULL,
  token varchar(200) DEFAULT '',
  token_type enum('access_token') DEFAULT 'access_token',
  status enum('active','expired') DEFAULT 'active',
  expiry varchar(30) DEFAULT '',
  access_count int(11) DEFAULT NULL,
  created_date datetime DEFAULT '0000-00-00 00:00:00',
  modified_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  is_delete enum('0','1') DEFAULT '0',
  is_testdata varchar(10) NOT NULL DEFAULT 'no',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE category (
  id int(11) NOT NULL,
  category_name varchar(200) NOT NULL,
  category_image varchar(200) NOT NULL,
  parent_id int(11) NOT NULL DEFAULT '0',
  created_date datetime NOT NULL,
  modified_date datetime DEFAULT NULL,
  is_delete enum('0','1') NOT NULL DEFAULT '0',
  is_test enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE favourite (
  id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  is_favourite enum('0','1','2') NOT NULL DEFAULT '0',
  created_date datetime NOT NULL,
  modified_date datetime DEFAULT NULL,
  is_delete enum('0','1') NOT NULL DEFAULT '0',
  is_test enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE history (
  id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  created_date datetime DEFAULT NULL,
  modified_date datetime DEFAULT NULL,
  is_delete enum('0','1') NOT NULL DEFAULT '0',
  is_test enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS product (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    barcode_id varchar(150) DEFAULT NULL,
    product_name varchar(100) NOT NULL,
    company_name varchar(100) DEFAULT NULL,
    calories float NOT NULL,
    ingredients text NOT NULL,
    product_image varchar(200) NOT NULL,
    saturated_fats float NOT NULL,
    fat_amount float NOT NULL,
    carbohydrate float NOT NULL,
    sugar float NOT NULL,
    dietary_fiber float NOT NULL,
    protein float NOT NULL,
    protein_amount float NOT NULL,
    salt float NOT NULL,
    sodium float NOT NULL,
    alcohol float NOT NULL,
    license_no varchar(100) DEFAULT NULL,
    category_id int(11) NOT NULL DEFAULT 0,
    created_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_delete enum('0','1') NOT NULL DEFAULT '0',
    is_test enum('0','1') NOT NULL DEFAULT '0',
    is_organic enum('1','0') NOT NULL DEFAULT '0',
    is_healthy enum('1','0') NOT NULL DEFAULT '0',
    PRIMARY KEY (id),
    UNIQUE KEY (barcode_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS pending_product (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    barcode_id varchar(150) DEFAULT NULL,
    product_name varchar(100) NOT NULL,
    company_name varchar(100) DEFAULT NULL,
    calories float NOT NULL,
    ingredients text NOT NULL,
    product_image varchar(200) NOT NULL,
    saturated_fats float NOT NULL,
    fat_amount float NOT NULL,
    carbohydrate float NOT NULL,
    sugar float NOT NULL,
    dietary_fiber float NOT NULL,
    protein float NOT NULL,
    protein_amount float NOT NULL,
    salt float NOT NULL,
    sodium float NOT NULL,
    alcohol float NOT NULL,
    license_no varchar(100) DEFAULT NULL,
    category_id int(11) NOT NULL DEFAULT 0,
    created_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_delete enum('0','1') NOT NULL DEFAULT '0',
    is_test enum('0','1') NOT NULL DEFAULT '0',
    is_organic enum('1','0') NOT NULL DEFAULT '0',
    is_healthy enum('1','0') NOT NULL DEFAULT '0',
    PRIMARY KEY (id),
    UNIQUE KEY (barcode_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE user (
  id int(11) UNSIGNED NOT NULL,
  email varchar(100) DEFAULT NULL,
  first_name varchar(100) DEFAULT NULL,
  last_name varchar(100) DEFAULT NULL,
  password varchar(100) DEFAULT NULL,
  facebook_id varchar(50) DEFAULT NULL,
  user_image varchar(200) DEFAULT NULL,
  device_token varchar(255) DEFAULT NULL,
  device_type enum('0','1') NOT NULL COMMENT '0- Android,  1- IOS',
  created_date datetime NOT NULL,
  modified_date datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  is_delete enum('0','1') NOT NULL DEFAULT '0',
  is_test enum('0','1') NOT NULL DEFAULT '0',
  guid varchar(100) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
