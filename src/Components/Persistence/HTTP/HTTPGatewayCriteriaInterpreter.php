<?php

namespace Manix\Brat\Components\Persistence\HTTP;

use Exception;
use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\CriteriaInterpreter;

class HTTPGatewayCriteriaInterpreter extends CriteriaInterpreter {

  public function patch($url, Criteria $criteria) {

    $url .= (strpos($url, '?') ? '&' : '?');

    foreach ($criteria->rules() as $rule) {
      if ($rule instanceof Criteria) {
        throw new Exception('Criteria subset not yet supported in HTTP gateway', 500);
      } else {
        foreach ($rule as $key => $data) {
          $fieldencoded = urlencode($data[0]);
          $dataencoded = urlencode($data[1]);
          $url .= "query[{$fieldencoded}]={$dataencoded}&fields[{$fieldencoded}]={$key}&";
        }
      }
    }

    return substr($url, 0, -1);
  }

  protected function btw($data, $values) {
    throw new Exception('Not supported yet', 500);
  }

  protected function eq($data, $value) {

  }

  protected function gt($data, $value) {

  }

  protected function in($data, $list) {

  }

  protected function like($data, $value) {

  }

  protected function lt($data, $value) {

  }

  protected function notbtw($data, $values) {

  }

  protected function noteq($data, $value) {

  }

  protected function notin($data, $list) {

  }

  protected function notlike($data, $value) {

  }

}
