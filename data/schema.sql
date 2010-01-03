DROP TABLE IF EXISTS amazon_product;
CREATE TABLE `amazon_product` (
  `id` int(11) NOT NULL auto_increment,
  `asin` varchar(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `small_image_url` varchar(255) default NULL,
  `medium_image_url` varchar(255) default NULL,
  `large_image_url` varchar(255) default NULL,
  `brand` varchar(255) default NULL,
  `platform` int(11) default NULL,
  `default_price` int(11) default NULL,
  `amazon_price` int(11) default NULL,
  `lowest_new_price` int(11) default NULL,
  `lowest_used_price` int(11) default NULL,
  `ean` varchar(13) default NULL,
  `average_review_rating` float default NULL,
  `total_review_num` int(11) default NULL,
  `release_at` date default NULL,
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`asin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS amazon_product_related_url;
CREATE TABLE `amazon_product_related_url` (
  `id` int(11) NOT NULL auto_increment,
  `asin` varchar(10) NOT NULL,
  `type` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `availablility` tinyint(4) NOT NULL default '1',
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS amazon_review;
CREATE TABLE `amazon_review` (
  `id` int(11) NOT NULL auto_increment,
  `asin` varchar(10) NOT NULL,
  `amazon_customer_id` varchar(48) NOT NULL,
  `amazon_customer_name` varchar(255) default NULL,
  `rating` int(11) NOT NULL,
  `helpful_vote` int(11) default NULL,
  `total_vote` int(11) default NULL,
  `title` varchar(255) NOT NULL,
  `article` text NOT NULL,
  `comment_at` date default NULL,
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`asin`,`amazon_customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS amazon_similar_product;
CREATE TABLE `amazon_similar_product` (
  `id` int(11) NOT NULL auto_increment,
  `root_asin` varchar(10) NOT NULL,
  `similar_asin` varchar(10) NOT NULL,
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`root_asin`,`similar_asin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS test_table;
CREATE TABLE `test_table` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

