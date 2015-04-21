<?php
namespace Muffin\Tags\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class TagBehavior extends Behavior
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'delimiter' => ',',
        'separator' => ':',
        'namespace' => null,
        'tagsAlias' => 'Tags',
        'tagsAssoc' => [
            'className' => 'Muffin/Tags.Tags',
            'joinTable' => 'tags_tagged',
            'foreignKey' => 'entity_id',
            'targetForeignKey' => 'tag_id',
            'propertyName' => 'tags',
        ],
        'tagsCounter' => ['counter'],
        'taggedAlias' => 'Tagged',
        'taggedAssoc' => [
            'className' => 'Muffin/Tags.Tagged',
        ],
        'taggedCounter' => ['tag_count'],
        'implementedEvents' => [
            'Model.beforeMarshal' => 'beforeMarshal',
        ],
        'implementedMethods' => [
            'normalizeTags' => 'normalizeTags',
        ],
    ];

    /**
     * {inheritdoc}
     */
    public function initialize(array $config)
    {
        $tagsAssoc = $this->config('tagsAssoc');
        $taggedAssoc = $this->config('taggedAssoc');

        $tagsAssoc += ['through' => $taggedAssoc['className']];

        $this->_configWrite('tagsAssoc', $tagsAssoc);
        $this->bindAssociations();
        $this->attachCounters();
    }

    /**
     * {@inheritdoc}
     */
    public function implementedEvents()
    {
        return $this->config('implementedEvents');
    }

    /**
     * {@inheritdoc}
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $field = $this->config('tagsAssoc.propertyName');

        if (!empty($data[$field]) && (!is_array($data[$field]) || !array_key_exists('_ids', $data[$field]))) {
            $data[$field] = $this->normalizeTags($data[$field]);
        }
    }

    /**
     * Binds all required associations if an association of the same name has
     * not already been configured.
     *
     * @return void
     */
    public function bindAssociations()
    {
        $config = $this->config();
        $tagsAlias = $config['tagsAlias'];
        $tagsAssoc = $config['tagsAssoc'];
        $taggedAlias = $config['taggedAlias'];
        $taggedAssoc = $config['taggedAssoc'];

        $table = $this->_table;
        $tableAlias = $this->_table->alias();

        if (!$table->association($taggedAlias)) {
            $table->hasMany($taggedAlias, $taggedAssoc);
        }

        if (!$table->association($tagsAlias)) {
            $table->belongsToMany($tagsAlias, $tagsAssoc);
        }

        if (!$table->{$tagsAlias}->association($tableAlias)) {
            $table->{$tagsAlias}
                ->belongsToMany($tableAlias, [
                    'className' => $table->table()
                ] + $this->config('tagsAssoc'));
        }

        if (!$table->{$taggedAlias}->association($tableAlias)) {
            $table->{$taggedAlias}
                ->belongsTo($tableAlias, [
                    'className' => $table->table(),
                    'foreignKey' => $tagsAssoc['foreignKey'],
                    'conditions' => [$table->{$taggedAlias}->aliasField('fk_table') => $table->table()],
                ]);
        }

        if (!$table->{$taggedAlias}->association($tableAlias . $tagsAlias)) {
            $table->{$taggedAlias}
                ->belongsTo($tableAlias . $tagsAlias, [
                    'className' => $tagsAssoc['className'],
                    'foreignKey' => $tagsAssoc['targetForeignKey'],
                    'conditions' => [$table->{$taggedAlias}->aliasField('fk_table') => $table->table()],
                ]);
        }
    }

    /**
     * Attaches the `CounterCache` behavior to the `Tagged` table to keep counts
     * on both the `Tags` and the tagged entities.
     *
     * @return void
     */
    public function attachCounters()
    {
        $config = $this->config();
        $tagsAlias = $config['tagsAlias'];
        $taggedAlias = $config['taggedAlias'];

        $taggedTable = $this->_table->{$taggedAlias};

        if (!$taggedTable->hasBehavior('CounterCache')) {
            $taggedTable->addBehavior('CounterCache');
        }

        $counterCache = $taggedTable->behaviors()->CounterCache;

        if (!$counterCache->config($tagsAlias)) {
            $counterCache->config($tagsAlias, $config['tagsCounter']);
        }

        if (!$counterCache->config($taggedAlias)) {
            $counterCache->config($this->_table->alias(), $config['taggedCounter']);
        }
    }

    /**
     * Normalizes tags.
     *
     * @param array|string $tags List of tags as an array or a delimited string (comma by default).
     * @return array Normalized tags valid to be marshaled.
     */
    public function normalizeTags($tags)
    {
        if (is_string($tags)) {
            $tags = explode($this->config('delimiter'), $tags);
        }

        $result = [];

        $common = ['_joinData' => ['fk_table' => $this->_table->table()]];
        if ($namespace = $this->config('namespace')) {
            $common += compact('namespace');
        }

        $tagsTable = $this->_table->{$this->config('tagsAlias')};
        $pk = $tagsTable->primaryKey();
        $df = $tagsTable->displayField();

        foreach ($tags as $tag) {
            list($id, $label) = $this->_normalizeTag($tag);
            $result[] = $common + compact(empty($id) ? $df : $pk);
        }

        return $result;
    }

    /**
     * Normalizes a tag string by trimming unnecessary whitespace and extracting the tag identifier
     * from a tag in case it exists.
     *
     * @param string $tag Tag.
     * @return array The tag's ID and label.
     */
    protected function _normalizeTag($tag)
    {
        $id = null;
        $label = $tag;
        $separator = $this->config('separator');
        if (strpos($tag, $separator) !== false) {
            list($id, $label) = explode($separator, $tag);
        }

        return [
            trim($id),
            trim($label)
        ];
    }
}
