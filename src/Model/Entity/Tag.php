<?php
namespace Muffin\Tags\Model\Entity;

use Cake\ORM\Entity;

class Tag extends Entity
{
    /**
     * {@inheritdoc}
     */
    public $accessible = [
        '*' => false,
        'label' => true,
    ];
}
