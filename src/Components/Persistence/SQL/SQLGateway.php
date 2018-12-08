<?php

namespace Manix\Brat\Components\Persistence\SQL;

use Manix\Brat\Components\Collection;
use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\Model;
use Manix\Brat\Components\Persistence\Gateway;
use Manix\Brat\Components\Persistence\SQL\Queries\DeleteQuery;
use Manix\Brat\Components\Persistence\SQL\Queries\InsertQuery;
use Manix\Brat\Components\Persistence\SQL\Queries\SelectQuery;
use Manix\Brat\Components\Sorter;
use Manix\Brat\Helpers\Arrays;
use PDO;

abstract class SQLGateway extends Gateway {

  protected $pdo;

  public function __construct(PDO $pdo) {
    parent::__construct();

    $this->setPDO($pdo);
  }

  public function getPDO() {
    return $this->pdo;
  }

  public function setPDO(PDO $pdo) {
    $this->pdo = $pdo;
    return $this;
  }

  public function find(...$pk): Collection {
    $criteria = new Criteria();

    foreach ($pk as $index => $value) {
      $criteria->equals($this->pk[$index], $value);
    }

    return $this->findBy($criteria);
  }

  public function findBy(Criteria $criteria): Collection {
    $query = new SelectQuery($this->table);

    $fields = [];
    foreach ($this->getFields() as $field) {
      $fields[] = $query->alias . '.' . $field;
    }
    $this->addJoins($query->columns(...$fields));

    $interpreter = new SQLGatewayCriteriaInterpreter();
    $interpreter->patch($query, $criteria);

    if ($this->sorter) {
      $query->order = implode(',', array_map(function($def) {
        return $def[0] . ' ' . ($def[1] === Sorter::ASC ? 'ASC' : 'DESC');
      }, $this->sorter->definitions()));
    }

    $query->limit($this->cutoff, $this->limit);

//    echo "<pre>" . print_r($query->build(), true) . "</pre>";
//    echo "<pre>" . print_r($query->data(), true) . "</pre>";
//    exit;
    $stmt = $this->pdo->prepare($query->build());
    $stmt->execute($query->data());

    return $this->parseJoins($stmt->fetchAll(), new Arrays());
  }

  public function persist(Model $model, array $fields = null): bool {
    $query = new InsertQuery($this->table);
    $data = [];

    if ($fields === null) {
      $fields = $this->getFields();
    }

    foreach ($fields as $field) {
      $query->addColumn($field);
      $data[$field] = $model->$field ?? null;
    }

    $data = $this->pack($data);

    $stmt = $this->pdo->prepare($query->insert($data)->onDuplicateKey(true)->build());
    $stmt->execute($query->data());

    $status = (bool)$stmt->rowCount();

    if ($status && $this->ai !== null && empty($model->{$this->ai})) {
      $data[$this->ai] = $this->pdo->lastInsertId();
    }

    $model->fill($this->unpack($data));

    return $status;
  }

  public function wipe(...$pk): bool {
    $criteria = new Criteria();

    foreach ($pk as $index => $value) {
      $criteria->equals($this->pk[$index], $value);
    }

    return $this->wipeBy($criteria);
  }

  public function wipeBy(Criteria $criteria): bool {
    $query = new DeleteQuery($this->table);
    $interpreter = new SQLGatewayCriteriaInterpreter();
    $interpreter->patch($query, $criteria);

    $stmt = $this->pdo->prepare($query->build());
    $stmt->execute($query->data());

    return (bool)$stmt->rowCount();
  }

  protected function produceJoinRule(Criteria $criteria) {
    $dummy = new SelectQuery();
    switch ($criteria->glue()) {
      case 'OR':
        $method = 'orWhere';
        break;

      default:
        $method = 'where';
        break;
    }

    $map = ['eq' =>
        '=',
        'noteq' =>
        '!=',
        'gt' =>
        '>',
        'lt' =>
        '<',
        'in' =>
        'IN',
        'notin' =>
        'NOT IN',
        'btw' =>
        'BETWEEN',
        'notbtw' =>
        'NOT BETWEEN',
        'like' =>
        'LIKE',
        'notlike' =>
        'NOT LIKE',];


    foreach ($criteria->rules() as $rule) {
      foreach ($rule as $key => $data) {
        $dummy->$method($data[0], $map[$key], $data[1]);
      }
    }

    $rule = str_replace($dummy->getSelectClause() . '  WHERE ', '', $dummy->build());
    foreach ($dummy->data() as $placeholder => $value) {
      $rule = str_replace($placeholder, $value[0] === '`' ? $value : $this->pdo->quote($value), $rule);
    }

    return $rule;
  }

  protected function addJoins(SelectQuery $query, $base = null): SelectQuery {
    if (!empty($this->joins)) {
      foreach ($this->joins as $key => $gate) {
        $tblAlias = Query::getAlias();
        $colAlias = $base . '$' . $tblAlias . '$_';
        $gate->tmpJoinAlias = $tblAlias;
        $gate->tmpJoinAsList = empty($this->rel[$key][3]);
        $localalias = ($this->tmpJoinAlias ?? $query->alias);

        if (isset($gate->customJoiner)) {
          $joiner = $gate->customJoiner;
          $rule = $this->produceJoinRule($joiner(function ($field) use ($localalias) {
            return "`$localalias`.`$field`";
          }, function ($field) use ($tblAlias) {
            return "`$tblAlias`.`$field`";
          }));
        } else {
          $rule = $localalias . '.' . $this->getLocalRelationKey($key) . ' = ' . $tblAlias . '.' . $this->getRemoteRelationKey($key, $gate);
        }

        $gate->addJoins($query->join('LEFT', $gate->table . ' ' . $tblAlias, $rule), $colAlias);

        foreach ($gate->getFields() as $field) {
          $query->addColumn($tblAlias . '.' . $field . ' AS ' . $colAlias . $field);
        }
      }
    }

    return $query;
  }

  protected function parseJoins(array $set, Arrays $helper) {
    $joined = [];

    foreach ($set as $index => &$row) {
      if ($helper->isNull($row)) {
        continue;
      }

      $pks = '';
      foreach ($this->pk as $field) {
        $pks .= $row[$field] . '/';
      }
      $pks = substr($pks, 0, -1);

      if (empty($joined[$pks])) {
        $joined[$pks] = & $row;

        foreach ($this->joins as $key => $gate) {
          $joined[$pks][$key] = [];
        }
      }

      foreach ($this->joins as $key => $gate) {
        $joined[$pks][$key][] = $helper->prefixFilter($row, '$' . $gate->tmpJoinAlias . '$_', true);
      }

      unset($set[$index]);
    }
    if (!empty($this->joins)) {
      foreach ($joined as $pks => $data) {
        foreach ($this->joins as $key => $gate) {
          $joined[$pks][$key] = $gate->parseJoins($data[$key], $helper);
        }
      }
    }

    return $this->instantiate($joined, $this->tmpJoinAsList ?? true);
  }

}
