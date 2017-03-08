<?php

namespace Manix\Brat\Components\Persistence;

use Manix\Brat\Components\Collection;
use Manix\Brat\Components\Criteria;
use Manix\Brat\Components\Model;

/**
 * The base gateway interface.
 */
abstract class Gateway {

    /**
     * The model interface.
     */
    const MODEL = Model::class;

    /**
     * @var string The table/namespace for this persistence element.
     */
    protected $table;

    /**
     * @var array List of fields to extract and persist per model instance.
     */
    protected $fields = [];

    /**
     * @var string An auto-increment field, if present.
     */
    protected $ai;

    /**
     * @var array The primary key declaration.
     */
    protected $pk;

    /**
     * @var array An array containing declarations of the relations of this gateway.
     */
    protected $rel;

    /**
     * @var array An array containing the currently joined gateways.
     */
    protected $joins = [];

    /**
     * Persist an object.
     * 
     * @param Model $model The model.
     * @param array $fields The fields to persist from the model. If null, then persist all.
     * @return bool Whether the model was persisted successfully or not.
     */
    abstract public function persist(Model $model, array $fields = null): bool;

    /**
     * Persist a collection of objects.
     * @param Collection $collection
     * @param array $fields
     * @return int The number of successfully persisted objects.
     */
    public function persistCollecion(Collection $collection, array $fields = null): int {
        $count = 0;

        foreach ($collection as $object) {
            $count += $this->persist($object, $fields) ? 1 : 0;
        }

        return $count;
    }

    /**
     * Wipe a persisted object.
     * 
     * @param array $pk Values for primary key.
     * @return bool Whether object was wiped successfully or not.
     */
    abstract public function wipe(...$pk): bool;

    abstract public function wipeBy(Criteria $criteria): bool;

    /**
     * Find a persisted object by primary key.
     * 
     * @param array $pk Values for primary key.
     * @return Collection The found objects.
     */
    abstract public function find(...$pk): Collection;

    abstract public function findBy(Criteria $criteria): Collection;

    protected function instantiate(array $set) {
        $interface = static::MODEL;
        $collection = new Collection($interface);

        foreach ($set as $row) {
            $collection->push(new $interface($row));
        }

        return $collection;
    }

    /**
     * Joins a predefined related gateway.
     * @param string $rel The key for the relation.
     * @return $gate
     */
    public function join($rel, Gateway $gate): Gateway {
        if (isset($this->rel[$rel]) && $gate instanceof $this->rel[$rel][0]) {
            $this->joins[$rel] = $gate;
            return $gate;
        }
    }

    /**
     * Remove a previously joined gateway.
     * @param string $rel The key for the relation.
     */
    public function unjoin($rel) {
        unset($this->joins[$rel]);
    }

}
