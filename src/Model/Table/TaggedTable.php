<?php
declare(strict_types=1);

namespace Muffin\Tags\Model\Table;

use Cake\ORM\Table;

/**
 * TaggedTable
 */
class TaggedTable extends Table
{
    /**
     * Initialize table config.
     *
     * @param array $config Config options
     * @return void
     */
    public function initialize(array $config): void
    {
        $this->setTable('tags_tagged');
        $this->addBehavior('Timestamp');
    }
}
