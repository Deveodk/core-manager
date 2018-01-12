<?php

namespace DeveoDK\Tests\Repositories;

use DeveoDK\Core\Manager\Databases\Entity;
use DeveoDK\Core\Manager\Repositories\Repository;
use DeveoDK\Tests\Databases\DummyEntity;

class DummyRepository extends Repository
{

    /**
     * Return the Entity the repository should use.
     * @return Entity
     */
    public function getEntity()
    {
        return new DummyEntity();
    }
}
