<?php
declare(strict_types=1);

namespace Muffin\Tags\Test\App\Model\Entity;

use Cake\ORM\Entity;
use Muffin\Tags\Model\Entity\TagAwareTrait;

class TagsMuffin extends Entity
{
    use TagAwareTrait;

    public function source($source = null): string
    {
        return 'Muffin/Tags.Muffins';
    }
}
