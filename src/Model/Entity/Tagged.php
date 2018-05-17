<?php
namespace Muffin\Tags\Model\Entity;

use Cake\ORM\Entity;

/**
 * Tagged
 */
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
