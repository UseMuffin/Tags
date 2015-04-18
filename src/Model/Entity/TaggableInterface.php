<?php
namespace Muffin\Tags\Model\Entity;

use Cake\Datasource\EntityInterface;

interface TaggableInterface implements EntityInterface
{
    public function tag($tags);
    public function untag($tags);
}
