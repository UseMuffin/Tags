<?php
namespace Muffin\Tags\Model\Entity;

use Cake\ORM\Entity;

class Tagged extends Entity
{

    /**
     * List of properties that can be mass assigned.
     *
     * @var array
     */
    public $accessible = [
        '*' => false,
    ];
}
