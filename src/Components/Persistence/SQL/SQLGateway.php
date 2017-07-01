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

class SQLGateway extends Gateway {

  protected $pdo;

  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
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
    
    $stmt = $this->pdo->prepare($query->insert($this->pack($data))->onDuplicateKey(true)->build());
    $stmt->execute($query->data());
    
    $status = (bool)$stmt->rowCount();

    if ($status && $this->ai !== null && empty($model->{$this->ai})) {
      $model->{$this->ai} = $this->pdo->lastInsertId();
    }

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

  protected function addJoins(SelectQuery $query, $base = null): SelectQuery {
    if (!empty($this->joins)) {
      foreach ($this->joins as $key => $gate) {
        $alias = Query::getAlias();
        $colAlias = $base . '$' . $alias . '$_';
        $gate->tmpJoinAlias = $alias;
        $gate->addJoins($query->join('LEFT', $gate->table . ' ' . $alias, ($this->tmpJoinAlias ?? $query->alias) . '.' . $this->rel[$key][1] . ' = ' . $alias . '.' . $this->rel[$key][2]), $colAlias);

        foreach ($gate->fields as $field) {
          $query->addColumn($alias . '.' . $field . ' AS ' . $colAlias . $field);
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

    return $this->instantiate($joined);
  }

}
