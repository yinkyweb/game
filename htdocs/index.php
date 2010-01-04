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

// $sql = 'select * from amazon_product where release_at > CURDATE() order by release_at asc';
$sql = 'SELECT * FROM amazon_product WHERE release_at > CURDATE() ORDER BY release_at ASC';
$sth = $dbh->prepare($sql);
$sth->execute();
$new_games = $sth->fetchAll();

$smarty = new MySmarty();
$smarty->assign('foo', 'bar');
$smarty->assign('conf', $conf);
$smarty->assign('new_games', $new_games);
$smarty->display('index.tpl');
