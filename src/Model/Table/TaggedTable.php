<?php
namespace Muffin\Tags\Model\Table;

use Cake\ORM\Table;

class TaggedTable extends Table
{
    public function initialize(array $config)
    {
        $this->table('tags_tagged');
    }
}
