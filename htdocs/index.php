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

$sql = '
SELECT
 product.asin asin,
 product.title title,
 product.medium_image_url medium_image_url,
 product.brand brand,
 product.default_price default_price,
 product.amazon_price amazon_price,
 product.lowest_new_price lowest_new_price,
 product.lowest_used_price lowest_used_price,
 DATE_FORMAT(product.release_at, :DATE_FORMAT) release_at,
 platform.japanese_name
FROM
 amazon_product product
LEFT JOIN
  amazon_platform platform
 ON
  product.platform = platform.amazon_platform_id
WHERE product.release_at > CURDATE()
ORDER BY product.release_at ASC LIMIT 20
';
$sth = $dbh->prepare($sql);
$format_str = '%Y年%m月%d日';
$sth->bindParam(':DATE_FORMAT', $format_str, PDO::PARAM_STR);
$sth->execute();
$new_games = $sth->fetchAll();

$smarty = new MySmarty();
$smarty->assign('conf', $conf);
$smarty->assign('new_games', $new_games);
$smarty->display('index.tpl');
