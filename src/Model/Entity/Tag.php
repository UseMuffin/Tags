<?php
declare(strict_types=1);

namespace Muffin\Tags\Model\Entity;

use Cake\ORM\Entity;

class Tag extends Entity
{
    /**
     * List of properties that can be mass assigned.
     *
     * @var array
     */
    public $accessible = [
        '*' => false,
        'label' => true,
    ];
}
