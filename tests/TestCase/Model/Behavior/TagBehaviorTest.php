<?php
namespace Muffin\Tags\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Muffin\Tags\Model\Behavior\TagBehavior;

/**
 * TagBehaviorTest
 */
class TagBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.Muffin/Tags.Buns',
        'plugin.Muffin/Tags.Muffins',
        'plugin.Muffin/Tags.Tagged',
        'plugin.Muffin/Tags.Tags',
    ];

    public function setUp()
    {
        parent::setUp();

        $table = TableRegistry::get('Muffin/Tags.Muffins', ['table' => 'tags_muffins']);
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

    public function testSavingDuplicates()
    {
        $entity = $this->Table->newEntity([
            'name' => 'Duplicate Tags?',
            'tags' => 'Color, Dark Color'
        ]);
        $this->Table->save($entity);
        $Tags = $this->Table->Tagged->Tags;
        $count = $Tags->find()->where(['label' => 'Color'])->count();
        $this->assertEquals(1, $count);
        $count = $Tags->find()->where(['label' => 'Dark Color'])->count();
        $this->assertEquals(1, $count);
    }

    public function testDefaultInitialize()
    {
        $belongsToMany = $this->Table->getAssociation('Tags');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsToMany', $belongsToMany);

        $hasMany = $this->Table->getAssociation('Tagged');
        $this->AssertInstanceOf('Cake\ORM\Association\HasMany', $hasMany);
    }

    public function testCustomInitialize()
    {
        $this->Table->removeBehavior('Tag');
        $this->Table->addBehavior('Muffin/Tags.Tag', [
            'tagsAlias' => 'Labels',
            'taggedAlias' => 'Labelled',
        ]);

        $belongsToMany = $this->Table->getAssociation('Labels');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsToMany', $belongsToMany);

        $hasMany = $this->Table->getAssociation('Labelled');
        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $hasMany);
    }

    public function testNormalizeTags()
    {
        $result = $this->Behavior->normalizeTags('foo, 3:foobar, bar');
        $expected = [
            0 => [
                '_joinData' => [
                    'fk_table' => 'tags_muffins'
                ],
                'label' => 'foo',
                'tag_key' => 'foo'
            ],
            1 => [
                '_joinData' => [
                    'fk_table' => 'tags_muffins'
                ],
                'id' => '3',
                'tag_key' => '3-foobar'
            ],
            2 => [
                '_joinData' => [
                    'fk_table' => 'tags_muffins'
                ],
                'label' => 'bar',
                'tag_key' => 'bar'
            ]
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Behavior->normalizeTags(['foo', 'bar']);
        $expected = [
            0 => [
                '_joinData' => [
                    'fk_table' => 'tags_muffins'
                ],
                'label' => 'foo',
                'tag_key' => 'foo'
            ],
            1 => [
                '_joinData' => [
                    'fk_table' => 'tags_muffins'
                ],
                'label' => 'bar',
                'tag_key' => 'bar'
            ]
        ];

        $this->assertEquals($expected, $result);

        $result = $this->Behavior->normalizeTags('first, ');
        $expected = [
            [
                '_joinData' => [
                    'fk_table' => 'tags_muffins',
                ],
                'label' => 'first',
                'tag_key' => 'first',
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testMarshalingOnlyNewTags()
    {
        $data = [
            'name' => 'Muffin',
            'tags' => 'foo, bar',
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->isDirty('tags'));

        $data = [
            'name' => 'Muffin',
            'tags' => [
                'foo',
                'bar',
            ],
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->isDirty('tags'));
    }

    public function testMarshalingOnlyExistingTags()
    {
        $data = [
            'name' => 'Muffin',
            'tags' => '1:Color, 2:Dark Color',
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->isDirty('tags'));

        $data = [
            'name' => 'Muffin',
            'tags' => ['_ids' => [
                '1',
                '2',
            ]],
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->isDirty('tags'));
    }

    public function testMarshalingBothNewAndExistingTags()
    {
        $data = [
            'name' => 'Muffin',
            'tags' => '1:Color, foo',
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->isDirty('tags'));
    }

    public function testMarshalingWithEmptyTagsString()
    {
        $data = [
            'name' => 'Muffin',
            'tags' => '',
        ];

        $entity = $this->Table->newEntity($data);
        $this->assertEquals(0, count((array)$entity->get('tags')));
    }

    public function testSaveIncrementsCounter()
    {
        $data = [
            'name' => 'Muffin',
            'tags' => '1:Color, 2:Dark Color',
        ];

        $counter = $this->Table->Tags->get(1)->counter;
        $entity = $this->Table->newEntity($data);

        $this->Table->save($entity);

        $result = $this->Table->Tags->get(1)->counter;
        $expected = $counter + 1;
        $this->assertEquals($expected, $result);

        $result = $this->Table->get($entity->id)->tag_count;
        $expected = 2;
        $this->assertEquals($expected, $result);
    }

    public function testCounterCacheDisabled()
    {
        $this->Table->removeBehavior('Tag');
        $this->Table->Tagged->removeBehavior('CounterCache');

        $this->Table->addBehavior('Muffin/Tags.Tag', [
            'taggedCounter' => false
        ]);

        $count = $this->Table->get(1)->tag_count;

        $data = [
            'id' => 1,
            'tags' => '1:Color, 2:Dark Color, new color',
        ];

        $entity = $this->Table->newEntity($data);
        $this->Table->save($entity);

        $result = $this->Table->get(1)->tag_count;
        $this->assertEquals($count, $result);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Field "non_existent" does not exist in table "tags_buns"
     */
    public function testCounterCacheFieldException()
    {
        $table = TableRegistry::get('Muffin/Tags.Buns', ['table' => 'tags_buns']);
        $table->addBehavior('Muffin/Tags.Tag', [
            'taggedCounter' => [
                'non_existent' => []
            ]
        ]);
    }

    public function testAssociationConditionsAreWorkingAsExpected()
    {
        $this->assertEquals(2, count($this->Table->get(1, ['contain' => ['Tags']])->tags));
    }
}
