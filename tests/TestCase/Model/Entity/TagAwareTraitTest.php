<?php
namespace Muffin\Tags\Test\TestCase\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Muffin\Tags\Model\Entity\TagAwareTrait;

class TagsMuffin extends Entity
{
    use TagAwareTrait;

    public function source($source = null)
    {
        return 'Muffins';
    }
}

class TagAwareTraitTest extends TestCase
{
    public $fixtures = [
        'Muffins' => 'plugin.muffin/tags.muffins',
        'Muffin/Tags.Tagged' => 'plugin.muffin/tags.tagged',
        'Muffin/Tags.Tags' => 'plugin.muffin/tags.tags',
    ];

    public function setUp()
    {
        parent::setUp();

        $table = TableRegistry::get('Muffins', ['table' => 'tags_muffins']);
        $table->addBehavior('Muffin/Tags.Tag');

        $this->Table = $table;
        $this->Behavior = $table->behaviors()->Tag;
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Behavior);
    }

    public function testTag()
    {
        $entity = new TagsMuffin(['id' => 1]);
        $entity->tag('new');
        $this->assertEquals(3, $this->Table->get(1)->tag_count);
    }

    public function testUntag()
    {
        $entity = new TagsMuffin(['id' => 1]);
        $entity->untag('Color');
        $this->assertEquals(1, $this->Table->get(1)->tag_count);
    }
}
