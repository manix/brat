<?php

namespace Manix\Brat\Components\Persistence\HTTP;

use Exception;
use Manix\Brat\Components\Collection;
use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\Model;
use Manix\Brat\Components\Persistence\Gateway;

abstract class HTTPGateway extends Gateway {

  /**
   * @var boolean Skip verifying peer and host
   */
  protected $skipSSLVerification = DEBUG_MODE;

  abstract function getURL();

  public function getCreateURL() {
    return $this->getURL();
  }

  public function getReadURL() {
    return $this->getURL();
  }

  public function getUpdateURL() {
    return $this->getURL();
  }

  public function getDeleteURL() {
    return $this->getURL();
  }

  public function curlinit() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_USERAGENT => 'Brat server',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json'
        ]
    ));

    if ($this->skipSSLVerification) {
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }

    return $curl;
  }

  public function findBy(Criteria $criteria): Collection {
    $curl = $this->curlinit();
    $url = $this->getURL();
    $url .= (strpos($url, '?') ? '&' : '?') . 'columns=' . urlencode(implode(',', $this->fields));
    $interpreter = new HTTPGatewayCriteriaInterpreter;
    $url = $interpreter->patch($url, $criteria);

    curl_setopt($curl, CURLOPT_URL, $url);

    $resp = curl_exec($curl);

    curl_close($curl);

    return $this->instantiate(json_decode($resp, true)[0]);
  }

  public function persist(Model $model, array $fields = null): bool {
    throw new Exception('HTTPGateway persist not yet implemented.', 500);
  }

  public function wipe(...$pk): bool {
    throw new Exception('HTTPGateway wipe not yet implemented.', 500);
  }

  public function wipeBy(Criteria $criteria): bool {
    throw new Exception('HTTPGateway can only wipe by primary key at this point.', 500);
  }

}
