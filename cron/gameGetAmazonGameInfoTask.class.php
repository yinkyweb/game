<?php

/**
 * gameGetAmazonGameInfoTask.class.php
 *
 * PHP versions 5
 *
 * @category  game
 * @package   game
 * @author    Yasunori Mahata <nori@mahata.net>
 * @copyright 2009 mahata.net
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://mahata.net
 */

define('ACCESS_KEY_ID', '12PZGM3FVPA6P78SK5G2');
define('SECRET_ACCESS_KEY', 'Lkgt8DZOhPbay3XJw4yz8tKYHF9MkpD4ja89YZQe');
define('BASE_URL', 'http://ecs.amazonaws.jp/onca/xml');

/**
 * A class to fetch amazon data
 *
 * @category  game
 * @package   game
 * @author    Yasunori Mahata <nori@mahata.net>
 * @copyright 2009 mahata.net
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://mahata.net
 */
class gameGetAmazonGameInfoTask extends sfBaseTask
{
  /**
   * Do configuration of the script
   *
   * @return void
   */
  protected function configure()
  {
    $this->addArguments(array(
                          // new sfCommandArgument('first_product', sfCommandArgument::OPTIONAL, 'a first product to search'),
    ));

    $this->addOptions(array(
//      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
                        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
                        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
                        new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
                        // add your own options here
    ));

    $this->namespace        = 'game';
    $this->name             = 'getAmazonGameInfo';
    $this->briefDescription = 'Get Amazon Game Information.';
    $this->detailedDescription = <<<EOF
The [game:getAmazonGameInfo|INFO] task does things.
Call it with:

  [php symfony game:getAmazonGameInfo|INFO]
EOF;
  }

  /**
   * Main function
   *
   * @param array $arguments Arguments
   * @param array $options Options
   *
   * @return boolean
   */
  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $title_list = file(sfConfig::get("sf_root_dir") . '/lib/task/gamelist.txt');
    foreach ($title_list as $title) {
      $title = trim($title);
      if ('' === $title) {
        continue;
      }

      echo "fetching ... {$title}\n";
      $url = $this->get_amazon_url($title);
      echo "{$url}\n";
      $xml = simplexml_load_file($url);

      foreach ($xml->Items->Item as $item) {
        $this->insert_amazon_product($item);
        $this->insert_amazon_review($item);
        $this->insert_amazon_similar_product($item);
      }
    }

    return true;
  }

  private function insert_amazon_product($item)
  {
      $criteria = new Criteria();
      $criteria->add(AmazonProductPeer::ASIN, $item->ASIN, Criteria::EQUAL);
      $amazon_product = AmazonProductPeer::doSelectOne($criteria);
      if (is_null($amazon_product)) {
        echo "asin " . $item->ASIN . " doesn't exist yet.\n";
        $amazon_product = new AmazonProduct();
      } else {
        echo "asin " . $item->ASIN . " already exist.\n";
      }

      //$amazon_product = new AmazonProduct();
      $amazon_product->setAsin($item->ASIN);
      $amazon_product->setSmallImageUrl($item->SmallImage->URL);
      $amazon_product->setMediumImageUrl($item->MediumImage->URL);
      $amazon_product->setLargeImageUrl($item->LargeImage->URL);
      $amazon_product->setBrand($item->ItemAttributes->Brand);
      $amazon_product->setEan($item->ItemAttributes->EAN);
      // $amazon_product->setGenre($item->ItemAttributes->Genre);
      $amazon_product->setPlatform(sfConfig::get('app_platform_' . $item->ItemAttributes->HardwarePlatform));
      $amazon_product->setDefaultPrice($item->ItemAttributes->ListPrice->Amount);
      $amazon_product->setAmazonPrice(@$item->Offers->Offer->OfferListing->Price->Amount);
      $amazon_product->setLowestNewPrice(@$item->OfferSummary->LowestNewPrice->Amount);
      $amazon_product->setLowestUsedPrice(@$item->OfferSummary->LowestUsedPrice->Amount);
      $amazon_product->setReleaseAt($item->ItemAttributes->ReleaseDate);
      $amazon_product->setTitle($item->ItemAttributes->Title);

      echo $item->ItemAttributes->Title . ', ' . $item->ItemAttributes->HardwarePlatform . "\n";

      try {
        $amazon_product->save();
      } catch (Exception $e) {
        var_dump($e->getMessage());
      }
  }

  private function insert_amazon_review($item)
  {
      if (isset($item->CustomerReviews->Review)) {
        foreach ($item->CustomerReviews->Review as $review) {

          $customer_id = ('' === trim($review->Reviewer->CustomerId)) ? (md5($review->Content) . '__UNKNOWN_USER__') : $review->Reviewer->CustomerId;
          $customer_name = ('' === trim($review->Reviewer->Name)) ? '名無しさん' : $review->Reviewer->Name;

          $criteria = new Criteria();
          $criteria->add(AmazonReviewPeer::ASIN, $item->ASIN, Criteria::EQUAL);
          $criteria->add(AmazonReviewPeer::AMAZON_CUSTOMER_ID, $customer_id, Criteria::EQUAL);
          $amazon_review = AmazonReviewPeer::doSelectOne($criteria);
          if (is_null($amazon_review)) {
            echo "asin " . $item->ASIN . ", customer_id " . $review->Reviewer->CustomerId . " doesn't exist yet.\n";
            $amazon_review = new AmazonReview();
          } else {
            echo "asin " . $item->ASIN . ", customer_id " . $review->Reviewer->CustomerId . " already exist.\n";
          }

          // $amazon_review = new AmazonReview();
          $amazon_review->setAsin($item->ASIN);
          $amazon_review->setRating($review->Rating);
          $amazon_review->setHelpfulVote($review->HelpfulVotes);
          $amazon_review->setTotalVote($review->TotalVotes);
          $amazon_review->setAmazonCustomerId($customer_id);
          $amazon_review->setAmazonCustomerName($customer_name);
          $amazon_review->setTitle($review->Summary);
          $amazon_review->setArticle($review->Content);
          $amazon_review->setCommentAt($review->Date);

          try {
            $amazon_review->save();
          } catch (Exception $e) {
            var_dump($e->getMessage());
          }
        }
      }
  }

  private function insert_amazon_similar_product($item)
  {
    if (isset($item->SimilarProducts->SimilarProduct)) {
      foreach ($item->SimilarProducts->SimilarProduct as $similar_product) {

        $criteria = new Criteria();
        $criteria->add(AmazonSimilarProductPeer::ROOT_ASIN, $item->ASIN, Criteria::EQUAL);
        $criteria->add(AmazonSimilarProductPeer::SIMILAR_ASIN, $similar_product->ASIN, Criteria::EQUAL);
        $amazon_similar_product = AmazonSimilarProductPeer::doSelectOne($criteria);
        if (is_null($amazon_similar_product)) {
          echo "asin " . $item->ASIN . ", similar_id " . $similar_product->ASIN . " doesn't exist.\n";
          $amazon_similar_product = new AmazonSimilarProduct();
        } else {
          echo "asin " . $item->ASIN . ", similar_id " . $similar_product->ASIN . " already exist.\n";
        }

        // $amazon_similar_product = new AmazonSimilarProduct();
        $amazon_similar_product->setRootAsin($item->ASIN);
        $amazon_similar_product->setSimilarAsin($similar_product->ASIN);

        try {
          $amazon_similar_product->save();
        } catch (Exception $e) {
          var_dump($e->getMessage());
        }
      }
    }
  }

  private function get_amazon_url($query)
  {
    $params = array();
    $params['Service']        = 'AWSECommerceService';
    $params['AWSAccessKeyId'] = ACCESS_KEY_ID;
    $params['Version']        = '2009-03-31';
    $params['Operation']      = 'ItemSearch';
    $params['SearchIndex']    = 'VideoGames';
    $params['ResponseGroup']  = 'Large';
    $params['Keywords']       = $query;
    $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
    ksort($params);

    $canonical_string = '';
    foreach ($params as $k => $v) {
      $canonical_string .= '&' . $this->urlencode_rfc3986($k) . '=' . $this->urlencode_rfc3986($v);
    }
    $canonical_string = substr($canonical_string, 1);

    $parsed_url = parse_url(BASE_URL);
    $string_to_sign = "GET\n{$parsed_url['host']}\n{$parsed_url['path']}\n{$canonical_string}";
    $signature = base64_encode(hash_hmac('sha256', $string_to_sign, SECRET_ACCESS_KEY, true));

    return BASE_URL . '?' . $canonical_string . '&Signature=' . $this->urlencode_rfc3986($signature);
  }

  private function urlencode_rfc3986($str)
  {
    return str_replace('%7E', '~', rawurlencode($str));
  }
}
