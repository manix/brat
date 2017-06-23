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
        $this->dir = new Directory($dir . '/' . $this->table);
    }

    public function find(...$pk): Collection {
        $set = [];
        $path = $this->dir . '/' . join(self::PK_CONCAT, $pk);

        if (count($pk) < count($this->pk)) {
            foreach (glob($path . self::PK_CONCAT . '*') as $path) {
                $set[] = $this->performJoins($this->read($path));
            }
        } else {
            if (is_file($path)) {
                $set[] = $this->performJoins($this->read($path));
            }
        }

        return $this->instantiate($set);
    }

    public function findBy(Criteria $criteria): Collection {
        $set = [];
        $interpreter = new FilesystemGatewayCriteriaInterpreter($criteria);

        foreach ($this->dir->files() as $file) {
            $data = $this->read($file);

            if ($interpreter->validate($data)) {
                $set[] = $this->performJoins($data);
            }
        }
        
        // TOOD implement sorter

        return $this->instantiate($set);
    }

    public function persist(Model $model, array $fields = null): bool {
        $data = [];

        if ($fields === null) {
            $fields = $this->fields;
        }


        if ($this->ai !== null && empty($model->{$this->ai})) {
            $ai = $this->getAI() + 1;
            $model->{$this->ai} = $ai;

            if (!$this->setAI($ai)) {
                throw new Exception($this->t8('common', 'cantSaveAI', [$this->table]), 500);
            }
        }

        foreach ($fields as $field) {
            $data[$field] = $model->$field ?? null;
        }

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
    protected function getAI(): int {
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
        if (!empty($this->joins)) {
            foreach ($this->joins as $key => $gate) {
                $row[$key] = $gate->findBy((new Criteria)->equals($this->rel[$key][2], $row[$this->rel[$key][1]]));
            }
        }

        return $row;
    }

}
