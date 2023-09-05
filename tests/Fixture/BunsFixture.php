<?php
declare(strict_types=1);

namespace Muffin\Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BunsFixture extends TestFixture
{
    public string $table = 'tags_buns';

    public array $records = [
        [
            'name' => 'square',
            'tag_count' => 1,
        ],
        [
            'name' => 'round',
            'tag_count' => 1,
        ],
    ];
}
