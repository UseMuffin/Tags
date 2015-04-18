<?php
namespace Muffin\Tags\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Muffin\Tags\Model\Behavior\TagBehavior;

class TagBehaviorTest extends TestCase
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

    public function testDefaultInitialize()
    {
        $belongsToMany = $this->Table->association('Tags');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsToMany', $belongsToMany);

        $hasMany = $this->Table->association('Tagged');
        $this->AssertInstanceOf('Cake\ORM\Association\HasMany', $hasMany);
    }

    public function testCustomInitialize()
    {
        $this->Table->removeBehavior('Tag');
        $this->Table->addBehavior('Muffin/Tags.Tag', [
            'tagsAlias' => 'Labels',
            'taggedAlias' => 'Labelled',
        ]);

        $belongsToMany = $this->Table->association('Labels');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsToMany', $belongsToMany);

        $hasMany = $this->Table->association('Labelled');
        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $hasMany);
    }

    public function testNormalizeTags()
    {
        $result = $this->Behavior->normalizeTags('foo, 3:foobar, bar');
        $expected = [
            [
                'label' => 'foo',
                '_joinData' => [
                    'fk_table' => 'tags_muffins',
                ],
            ],
            [
                'id' => 3,
                '_joinData' => [
                    'fk_table' => 'tags_muffins',
                ],
            ],
            [
                'label' => 'bar',
                '_joinData' => [
                    'fk_table' => 'tags_muffins',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Behavior->normalizeTags(['foo', 'bar']);
        $expected = [
            [
                'label' => 'foo',
                '_joinData' => [
                    'fk_table' => 'tags_muffins',
                ],
            ],
            [
                'label' => 'bar',
                '_joinData' => [
                    'fk_table' => 'tags_muffins',
                ],
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
        $this->assertTrue($entity->dirty('tags'));

        $data = [
            'name' => 'Muffin',
            'tags' => [
                'foo',
                'bar',
            ],
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->dirty('tags'));
    }

    public function testMarshalingOnlyExistingTags()
    {
        $data = [
            'name' => 'Muffin',
            'tags' => '1:Color, 2:Dark Color',
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->dirty('tags'));

        $data = [
            'name' => 'Muffin',
            'tags' => ['_ids' => [
                '1',
                '2',
            ]],
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->dirty('tags'));
    }

    public function testMarshalingBothNewAndExistingTags()
    {
        $data = [
            'name' => 'Muffin',
            'tags' => '1:Color, foo',
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->dirty('tags'));
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

        $result = $this->Table->get($entity->id)->tags_cnt;
        $expected = 2;
        $this->assertEquals($expected, $result);
    }
}
