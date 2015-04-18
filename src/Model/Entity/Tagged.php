<?php
namespace Muffin\Tags\Model\Entity;

use Cake\ORM\Entity;

class Tagged extends Entity
{
    public $accessible = [
        '*' => false,
    ];
}
