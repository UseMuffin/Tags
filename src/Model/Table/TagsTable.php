<?php
namespace Muffin\Tags\Model\Table;

use Cake\Core\Plugin;
use Cake\ORM\Table;

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
        $method = 'isLoaded';
        if (!method_exists(Plugin::class, 'isLoaded')) {
            $method = 'loaded';
        }
        if (Plugin::{$method}('Muffin/Slug')) {
            $this->addBehavior('Muffin/Slug.Slug');
        }
    }
}
