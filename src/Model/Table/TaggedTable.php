<?php
namespace Muffin\Tags\Model\Table;

use Cake\ORM\Table;

class TaggedTable extends Table
{

    /**
     * Initialize table config.
     *
     * @param array $config Config options
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('tags_tagged');
        $this->belongsTo('Tags', [
            'className' => 'Muffin/Tags.Tags',
            'foreignKey' => 'tag_id',
            'propertyName' => 'tags',
        ]);
    }
}
