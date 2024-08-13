<?php

namespace Manix\Brat\Components\Persistence\FS;

use Exception;
use Manix\Brat\Components\Collection;
use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\Filesystem\Directory;
use Manix\Brat\Components\Filesystem\Factory;
use Manix\Brat\Components\Filesystem\File;
use Manix\Brat\Components\Model;
use Manix\Brat\Components\Persistence\Gateway;
use Manix\Brat\Components\Sorter;
use Manix\Brat\Components\Translator;

abstract class FilesystemGateway extends Gateway {

  const PK_CONCAT = '/';

  use Translator;

  protected $dir;
  protected static $cache;

  /**
   * A gateway for raw file system storage.
   * @param Directory $dir The root directory.
   */
  public function __construct(Directory $dir) {
    parent::__construct();
    
    $this->dir = new Directory($dir . '/' . $this->table);
  }

  public function find(...$pk): Collection {
    $set = [];
    $path = $this->dir . '/' . join(self::PK_CONCAT, $pk);
    $count = count($pk);
    $wildcard = $count === 0 && (count($this->pk) > 1) ? '**/*' : '*';

    if ($count < count($this->pk)) {
      foreach (glob(rtrim($path, '/') . self::PK_CONCAT . $wildcard) as $path) {
        $set[] = $this->performJoins($this->read($path));
      }
    } else {
      if (is_file($path)) {
        $set[] = $this->performJoins($this->read($path));
      }
    }

    $this->performSort($set);

    return $this->instantiate($set);
  }

  // public function sort(Sorter $sorter) {
  //   // TODO implement sorter in findBy(
  //   throw new Exception('Sorter has not yet been implemented in FilesystemGateway', 500);
  // }

  public function findBy(Criteria $criteria): Collection {
    $set = [];
    $interpreter = new FilesystemGatewayCriteriaInterpreter($criteria);

    foreach ($this->dir->files() as $file) {
      $data = $this->read($file);

      if ($interpreter->validate($data)) {
        $set[] = $this->performJoins($data);
      }
    }

    $this->performSort($set);

    return $this->instantiate($set);
  }

  public function performSort(&$set) {
    if (!$this->sorter || empty($this->sorter->definitions())) {
      return;
    }

    $d = $this->sorter->definitions();

    // TODO 
    // currently only one field sort is supported,
    // implement multi-field sort
    uasort($set, function($a, $b) use ($d) { 
      return strcasecmp($a[$d[0][0]], $b[$d[0][0]]) * ($d[0][1] ? -1 : 1);
    });
  }

  public function persist(Model $model, array $fields = null): bool {
    $data = [];

    if ($fields === null) {
      $fields = $this->getFields();
    }

    foreach ($fields as $field) {
      $data[$field] = $model->$field ?? null;
    }

    if ($this->ai !== null && empty($model->{$this->ai})) {
      $ai = $this->getLastAIValue() + 1;
      $data[$this->ai] = $ai;

      if (!$this->setAI($ai)) {
        throw new Exception($this->t8('common', 'cantSaveAI', [$this->table]), 500);
      }
    }

    $data = $this->pack($data);

    // Set timestamps, AI and other generated data back into the model.
    $model->fill($this->unpack($data));

    return (bool)file_put_contents(new File($this->dir . '/' . $this->getPKString($model)), serialize($data));
  }

  public function wipe(...$pk): bool {
    $path = $this->dir . '/' . join(self::PK_CONCAT, $pk);

    try {
      return (new Factory())->get($path)->delete();
    } catch (Exception $ex) {
      
    }

    return false;
  }

  public function wipeBy(Criteria $criteria): bool {
    $interpreter = new FilesystemGatewayCriteriaInterpreter($criteria);

    foreach ($this->dir->files() as $file) {
      $data = $this->read($file);

      if ($interpreter->validate($data)) {
        if (!$file->delete()) {
          $fail = true;
        }
      }
    }

    return !isset($fail);
  }

  /**
   * Get a string uniquely identifying this object by primary key.
   * @param Model $model
   * @return string The PK string.
   */
  public function getPKString(Model $model) {
    $fields = [];

    foreach ($this->pk as $field) {
      $fields[] = $model->$field;
    }

    return join(self::PK_CONCAT, $fields);
  }

  /**
   * Get the current last auto increment value for the table.
   * @return int The last AI value.
   */
  protected function getLastAIValue(): int {
    return (int)file_get_contents(new File($this->dir . '/.meta'));
  }

  /**
   * Set a new last AI value.
   * @param int $ai The new last AI value
   * @return bool Whether saving the AI value was successful or not.
   */
  protected function setAI(int $ai): bool {
    return (bool)file_put_contents($this->dir . '/.meta', $ai);
  }

  /**
   * Read a stored object's information.
   * @param string $path
   * @return array Stored data.
   */
  protected function read(string $path) {
    return unserialize(file_get_contents($path));
  }

  protected function performJoins($row) {
    // TODO make flexible via new design, see SQL gateway
    if (!empty($this->joins)) {
      foreach ($this->joins as $key => $gate) {
        $row[$key] = $gate->findBy((new Criteria)->equals($this->getRemoteRelationKey($key, $gate), $row[$this->getLocalRelationKey($key)]));
      }
    }

    return $row;
  }

}
