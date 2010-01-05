<?php

require_once (dirname(__FILE__) . '/../lib/smarty/libs/MySmarty.class.php');

// read configurations
$conf = parse_ini_file(dirname(__FILE__) . '/../config.ini' , true);

// create database connection
try {
  $dbh = new PDO(sprintf('mysql:dbname=%s;host=%s', $conf['db']['name'], $conf['db']['host']),
                 $conf['db']['user'],
                 $conf['db']['pass']);
} catch (PDOException $exception) {
  echo 'Connection failed: ' . $exception->getMessage();
}

/*
  `asin` varchar(10) NOT NULL,
  `amazon_customer_id` varchar(48) NOT NULL,
  `amazon_customer_name` varchar(255) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `helpful_vote` int(11) DEFAULT NULL,
  `total_vote` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `article` text NOT NULL,
  `comment_at` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
 */

$sql = '
SELECT
 amazon_customer_name,
 rating,
 helpful_vote,
 total_vote,
 title,
 article,
 DATE_FORMAT(comment_at, :DATE_FORMAT) comment_at
FROM
 amazon_review
WHERE asin = :ASIN;
';
$sth = $dbh->prepare($sql);
$sth->bindParam(':ASIN', $_GET['asin'], PDO::PARAM_STR);
$format_str = '%Y年%m月%d日';
$sth->bindParam(':DATE_FORMAT', $format_str, PDO::PARAM_STR);
$sth->execute();
$game_reviews = $sth->fetchAll();

// var_dump($game_reviews);

$smarty = new MySmarty();
$smarty->assign('conf', $conf);
$smarty->assign('game_reviews', $game_reviews);
$smarty->assign('asin', $_GET['asin']);
$smarty->display('item.tpl');
