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
        $this->table('tags_tags');
        $this->displayField('label');
        $this->addBehavior('Timestamp');
        if (Plugin::loaded('Muffin/Slug')) {
            $this->addBehavior('Muffin/Slug.Slug');
        }
    }
}
