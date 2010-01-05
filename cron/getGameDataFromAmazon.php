<?php

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

$title_list = file(dirname(__FILE__) . '/gamelist.txt');
foreach ($title_list as $title) {
  $title = trim($title);
  // debug
  if ('__EXIT__' === $title) {
    break;
  }
  if ('' === $title) {
    continue;
  }

  echo "fetching ... {$title}\n";
  $url = get_amazon_url($title, $conf);
  echo "{$url}\n";
  $xml = simplexml_load_file($url);

  foreach ($xml->Items->Item as $item) {
    insert_amazon_product($dbh, $item, $conf);
    insert_amazon_review($dbh, $item);
    insert_amazon_similar_product($dbh, $item);
  }
}

function insert_amazon_product($dbh, $item, $conf)
{
  $sql = '
INSERT INTO amazon_product
 (asin, title, small_image_url, medium_image_url, large_image_url, brand, platform, default_price, amazon_price, lowest_new_price, lowest_used_price, ean, release_at, created_at)
VALUES
 (:ASIN, :TITLE, :SMALL_IMAGE_URL, :MEDIUM_IMAGE_URL, :LARGE_IMAGE_URL, :BRAND, :PLATFORM, :DEFAULT_PRICE, :AMAZON_PRICE, :LOWEST_NEW_PRICE, :LOWEST_USED_PRICE, :EAN, :RELEASE_AT, :CREATED_AT)
';

  $sth = $dbh->prepare($sql);
  $sth->bindParam(':ASIN', $item->ASIN, PDO::PARAM_STR);
  $sth->bindParam(':TITLE', $item->ItemAttributes->Title, PDO::PARAM_STR);
  $sth->bindParam(':SMALL_IMAGE_URL', $item->SmallImage->URL, PDO::PARAM_STR);
  $sth->bindParam(':MEDIUM_IMAGE_URL', $item->MediumImage->URL, PDO::PARAM_STR);
  $sth->bindParam(':LARGE_IMAGE_URL', $item->LargeImage->URL, PDO::PARAM_STR);
  $sth->bindParam(':BRAND', $item->ItemAttributes->Brand, PDO::PARAM_STR);
  $sth->bindParam(':PLATFORM', $conf['platform'][(string)$item->ItemAttributes->HardwarePlatform], PDO::PARAM_INT);
  $sth->bindParam(':DEFAULT_PRICE', $item->ItemAttributes->ListPrice->Amount, PDO::PARAM_INT);
  $sth->bindParam(':AMAZON_PRICE', @$item->Offers->Offer->OfferListing->Price->Amount, PDO::PARAM_INT);
  $sth->bindParam(':LOWEST_NEW_PRICE', @$item->OfferSummary->LowestNewPrice->Amount, PDO::PARAM_INT);
  $sth->bindParam(':LOWEST_USED_PRICE', @$item->OfferSummary->LowestUsedPrice->Amount, PDO::PARAM_INT);
  $sth->bindParam(':EAN', $item->ItemAttributes->EAN, PDO::PARAM_STR);
  $tdate = date('Y-m-d H:i:s');
  $sth->bindParam(':RELEASE_AT', $item->ItemAttributes->ReleaseDate, PDO::PARAM_STR);
  $sth->bindParam(':CREATED_AT', $tdate, PDO::PARAM_STR);

  $sth->execute();
}

function insert_amazon_review($dbh, $item)
{
  if (isset($item->CustomerReviews->Review)) {
    foreach ($item->CustomerReviews->Review as $review) {
      $customer_id = ('' === trim($review->Reviewer->CustomerId)) ? (md5($review->Content) . '__UNKNOWN_USER__') : $review->Reviewer->CustomerId;
      $customer_name = ('' === trim($review->Reviewer->Name)) ? '名無しさん' : $review->Reviewer->Name;

      // check if the review already exists
      $tdate = date('Y-m-d H:i:s');
      $sql = 'SELECT id FROM amazon_review WHERE asin = :ASIN AND amazon_customer_id = :AMAZON_CUSTOMER_ID';
      $sth = $dbh->prepare($sql);
      $sth->bindParam(':ASIN', $item->ASIN, PDO::PARAM_STR);
      $sth->bindParam(':AMAZON_CUSTOMER_ID', $customer_id, PDO::PARAM_STR);
      $sth->execute();
      if ($sth->fetch()) {
        // UPDATE
        $sql = '
UPDATE amazon_review
 SET amazon_customer_name = :AMAZON_CUSTOMER_NAME, rating = :RATING, helpful_vote = :HELPFUL_VOTE, total_vote = :TOTAL_VOTE, title = :TITLE, article = :ARTICLE, comment_at = :COMMENT_AT, updated_at = :UPDATED_AT
WHERE
 asin = :ASIN AND amazon_customer_id = :AMAZON_CUSTOMER_ID
';
        $sth = $dbh->prepare($sql);
        $sth->bindParam(':UPDATED_AT', $tdate, PDO::PARAM_STR);
      } else {
        // INSERT
        $sql = '
INSERT INTO amazon_review
 (asin, amazon_customer_id, amazon_customer_name, rating, helpful_vote, total_vote, title, article, comment_at, created_at)
VALUES
 (:ASIN, :AMAZON_CUSTOMER_ID, :AMAZON_CUSTOMER_NAME, :RATING, :HELPFUL_VOTE, :TOTAL_VOTE, :TITLE, :ARTICLE, :COMMENT_AT, :CREATED_AT)
';
        $sth = $dbh->prepare($sql);
        $sth->bindParam(':CREATED_AT', $tdate, PDO::PARAM_STR);
      }
      $sth->bindParam(':ASIN', $item->ASIN, PDO::PARAM_STR);
      $sth->bindParam(':AMAZON_CUSTOMER_ID', $customer_id, PDO::PARAM_STR);
      $sth->bindParam(':AMAZON_CUSTOMER_NAME', $customer_name, PDO::PARAM_STR);
      $sth->bindParam(':RATING', $review->Rating, PDO::PARAM_INT);
      $sth->bindParam(':HELPFUL_VOTE', $review->HelpfulVotes, PDO::PARAM_INT);
      $sth->bindParam(':TOTAL_VOTE', $review->TotalVotes, PDO::PARAM_INT);
      $sth->bindParam(':TITLE', $review->Summary, PDO::PARAM_STR);
      $sth->bindParam(':ARTICLE', $review->Content, PDO::PARAM_STR);
      $sth->bindParam(':COMMENT_AT', $review->Date, PDO::PARAM_STR);

      $sth->execute();
    }
  }
}

function insert_amazon_similar_product($dbh, $item)
{
  // echo "similar: prev_check\n";
  // var_dump($item->SimilarProducts->SimilarProduct);
  if (isset($item->SimilarProducts->SimilarProduct)) {
    foreach ($item->SimilarProducts->SimilarProduct as $similar_product) {
      $sql = 'SELECT id FROM amazon_similar_product WHERE root_asin = :ROOT_ASIN AND SIMILAR_ASIN = :SIMILAR_ASIN';
      $sth = $dbh->prepare($sql);
      $sth->bindParam(':ROOT_ASIN', $item->ASIN, PDO::PARAM_STR);
      $sth->bindParam(':SIMILAR_ASIN', $similar_product->ASIN, PDO::PARAM_STR);
      $sth->execute();
      if ($sth->fetch()) {
        // UPDATE
        // do nothing
        // echo "similar: update\n";
      } else {
        // INSERT
        // echo "similar: insert\n";
        $sql = '
INSERT INTO amazon_similar_product
 (root_asin, similar_asin, created_at)
VALUES
 (:ROOT_ASIN, :SIMILAR_ASIN, :CREATED_AT)
';
        // var_dump($item->ASIN);
        // var_dump($similar_product->ASIN);

        $sth = $dbh->prepare($sql);
        $sth->bindParam(':ROOT_ASIN', $item->ASIN, PDO::PARAM_STR);
        $sth->bindParam(':SIMILAR_ASIN', $similar_product->ASIN, PDO::PARAM_STR);
        $tdate = date('Y-m-d H:i:s');
        $sth->bindParam(':CREATED_AT', $tdate, PDO::PARAM_STR);
        $sth->execute();
      }
    }
  }
}

function get_amazon_url($query, $conf)
{
  $params = array();
  $params['Service']        = 'AWSECommerceService';
  $params['AWSAccessKeyId'] = $conf['amazon']['access_key_id'];
  $params['Version']        = '2009-03-31';
  $params['Operation']      = 'ItemSearch';
  $params['SearchIndex']    = 'VideoGames';
  $params['ResponseGroup']  = 'Large';
  $params['Keywords']       = $query;
  $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
  ksort($params);

  $canonical_string = '';
  foreach ($params as $k => $v) {
    $canonical_string .= '&' . urlencode_rfc3986($k) . '=' . urlencode_rfc3986($v);
  }
  $canonical_string = substr($canonical_string, 1);

  $parsed_url = parse_url($conf['amazon']['base_url']);
  $string_to_sign = "GET\n{$parsed_url['host']}\n{$parsed_url['path']}\n{$canonical_string}";
  $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $conf['amazon']['secret_access_key'], true));

  return $conf['amazon']['base_url'] . '?' . $canonical_string . '&Signature=' . urlencode_rfc3986($signature);
}

function urlencode_rfc3986($str)
{
  return str_replace('%7E', '~', rawurlencode($str));
}


