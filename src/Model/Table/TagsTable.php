<?php
namespace Muffin\Tags\Model\Table;

use Cake\Core\Plugin;
use Cake\ORM\Table;

class TagsTable extends Table
{
    /**
     * Initialize
     *
     * @param array
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('tags_tags');
        $this->displayField('label');
        if (Plugin::loaded('Muffin/Slug')) {
            $this->addBehavior('Muffin/Slug.Slug');
        }
    }
}
