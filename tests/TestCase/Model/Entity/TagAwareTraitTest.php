<?php
declare(strict_types=1);

namespace Muffin\Tags\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;
use Muffin\Tags\Test\App\Model\Entity\TagsMuffin;

class TagAwareTraitTest extends TestCase
{
    public array $fixtures = [
        'plugin.Muffin/Tags.Muffins',
        'plugin.Muffin/Tags.Tagged',
        'plugin.Muffin/Tags.Tags',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $table = $this->getTableLocator()->get('Muffin/Tags.Muffins', ['table' => 'tags_muffins']);
        $table->addBehavior('Muffin/Tags.Tag');

        $this->Table = $table;
        $this->Behavior = $table->behaviors()->Tag;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->getTableLocator()->clear();
        unset($this->Behavior);
    }

    public function testTag(): void
    {
        $count = $this->Table->get(1)->tag_count;

        $entity = new TagsMuffin(['id' => 1]);
        $entity->tag('new');
        $this->assertEquals($count + 1, $this->Table->get(1)->tag_count);
    }

    public function testUntag(): void
    {
        $entity = new TagsMuffin(['id' => 1]);
        $entity->untag('Color');
        $this->assertEquals(1, $this->Table->get(1)->tag_count);
    }
}
