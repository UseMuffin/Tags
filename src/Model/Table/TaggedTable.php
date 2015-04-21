<?php
namespace Muffin\Tags\Model\Table;

use Cake\ORM\Table;

class TaggedTable extends Table
{
    /**
     * Initialize
     *
     * @param array
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('tags_tagged');
    }
}
