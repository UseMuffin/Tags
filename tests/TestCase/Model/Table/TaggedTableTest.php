<?php
namespace Muffin\Tags\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Muffin\Tags\Model\Table\TaggedTable;

/**
 * Muffin\Tags\Model\Table\TaggedTable Test Case
 */
class TaggedTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.muffin/tags.tags',
        'plugin.muffin/tags.tagged',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Tagged = TableRegistry::get('Muffin/Tags.Tagged', ['table' => 'tags_tagged']);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->Tagged);
        TableRegistry::clear();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $result = $this->Tagged->find()
            ->matching('Tags', function ($q) {
                return $q->where(['label' => 'Dark Color']);
            })
            ->all()
            ->count();
        
        $this->assertEquals($result, 2);
    }
}
