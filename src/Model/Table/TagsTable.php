<?php
namespace Muffin\Tags\Model\Table;

use Cake\Core\Plugin;
use Cake\ORM\Table;

/**
 * TagsTable
 */
class TagsTable extends Table
{

    /**
     * Initialize table config.
     *
     * @param array $config Config options
     * @return void
     */
    public function initialize(array $config)
    {
        $this->setTable('tags_tags');
        $this->setDisplayField('label');
        $this->addBehavior('Timestamp');
        if (Plugin::loaded('Muffin/Slug')) {
            $this->addBehavior('Muffin/Slug.Slug');
        }
    }
}
