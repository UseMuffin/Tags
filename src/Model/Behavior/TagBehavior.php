<?php
namespace Muffin\Tags\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\Utility\Text;
use RuntimeException;

/**
 * TagBehavior
 */
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
            'foreignKey' => 'fk_id',
            'targetForeignKey' => 'tag_id',
            'propertyName' => 'tags',
        ],
        'tagsCounter' => ['counter'],
        'taggedAlias' => 'Tagged',
        'taggedAssoc' => [
            'className' => 'Muffin/Tags.Tagged',
        ],
        'taggedCounter' => ['tag_count' => [
            'conditions' => []
        ]],
        'implementedEvents' => [
            'Model.beforeMarshal' => 'beforeMarshal',
        ],
        'implementedMethods' => [
            'normalizeTags' => 'normalizeTags',
        ],
        'fkTableField' => 'fk_table'
    ];

    /**
     * Initialize configuration.
     *
     * @param array $config Configuration array.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->bindAssociations();
        $this->attachCounters();
    }

    /**
     * Return lists of event's this behavior is interested in.
     *
     * @return array Events list.
     */
    public function implementedEvents()
    {
        return $this->getConfig('implementedEvents');
    }

    /**
     * Before marshal callaback
     *
     * @param \Cake\Event\Event $event The Model.beforeMarshal event.
     * @param \ArrayObject $data Data.
     * @param \ArrayObject $options Options.
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $field = $this->getConfig('tagsAssoc.propertyName');
        if (!empty($data[$field]) && (!is_array($data[$field]) || !array_key_exists('_ids', $data[$field]))) {
            $data[$field] = $this->normalizeTags($data[$field]);
        }
        if (isset($data[$field]) && empty($data[$field])) {
            unset($data[$field]);
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
        $config = $this->getConfig();
        $tagsAlias = $config['tagsAlias'];
        $tagsAssoc = $config['tagsAssoc'];
        $taggedAlias = $config['taggedAlias'];
        $taggedAssoc = $config['taggedAssoc'];

        $table = $this->_table;
        $tableAlias = $this->_table->getAlias();

        // 3.5 compatibility
        $hasAssociation = 'hasAssociation';
        if (!method_exists($table, 'hasAssociation')) {
            $hasAssociation = 'association';
        }

        $assocConditions = [$taggedAlias . '.' . $this->getConfig('fkTableField') => $table->getTable()];

        if (!$table->{$hasAssociation}($taggedAlias)) {
            $table->hasMany($taggedAlias, $taggedAssoc + [
                'foreignKey' => $tagsAssoc['foreignKey'],
                'conditions' => $assocConditions,
            ]);
        }

        if (!$table->{$hasAssociation}($tagsAlias)) {
            $table->belongsToMany($tagsAlias, $tagsAssoc + [
                'through' => $table->{$taggedAlias}->getTarget(),
                'conditions' => $assocConditions
            ]);
        }

        if (!$table->{$tagsAlias}->hasAssociation($tableAlias)) {
            $table->{$tagsAlias}
                ->belongsToMany($tableAlias, [
                    'className' => $table->getTable(),
                ] + $tagsAssoc);
        }

        if (!$table->{$taggedAlias}->hasAssociation($tableAlias)) {
            $table->{$taggedAlias}
                ->belongsTo($tableAlias, [
                    'className' => $table->getTable(),
                    'foreignKey' => $tagsAssoc['foreignKey'],
                    'conditions' => $assocConditions,
                    'joinType' => 'INNER',
                ]);
        }

        if (!$table->{$taggedAlias}->hasAssociation($tableAlias . $tagsAlias)) {
            $table->{$taggedAlias}
                ->belongsTo($tableAlias . $tagsAlias, [
                    'className' => $tagsAssoc['className'],
                    'foreignKey' => $tagsAssoc['targetForeignKey'],
                    'conditions' => $assocConditions,
                    'joinType' => 'INNER',
                ]);
        }
    }

    /**
     * Attaches the `CounterCache` behavior to the `Tagged` table to keep counts
     * on both the `Tags` and the tagged entities.
     *
     * @return void
     * @throws \RuntimeException If configured counter cache field does not exist in table.
     */
    public function attachCounters()
    {
        $config = $this->getConfig();
        $tagsAlias = $config['tagsAlias'];
        $taggedAlias = $config['taggedAlias'];

        $taggedTable = $this->_table->{$taggedAlias};

        if (!$taggedTable->hasBehavior('CounterCache')) {
            $taggedTable->addBehavior('CounterCache');
        }

        $counterCache = $taggedTable->behaviors()->CounterCache;

        if (!$counterCache->getConfig($tagsAlias)) {
            $counterCache->setConfig($tagsAlias, $config['tagsCounter']);
        }

        if ($config['taggedCounter'] === false) {
            return;
        }

        foreach ($config['taggedCounter'] as $field => $o) {
            if (!$this->_table->hasField($field)) {
                throw new RuntimeException(sprintf(
                    'Field "%s" does not exist in table "%s"',
                    $field,
                    $this->_table->getTable()
                ));
            }
        }

        if (!$counterCache->getConfig($taggedAlias)) {
            $config['taggedCounter']['tag_count']['conditions'] = [
                $taggedTable->aliasField($this->getConfig('fkTableField')) => $this->_table->getTable()
            ];
            $counterCache->setConfig($this->_table->getAlias(), $config['taggedCounter']);
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
            $tags = explode($this->getConfig('delimiter'), $tags);
        }

        $result = [];

        $common = ['_joinData' => [$this->getConfig('fkTableField') => $this->_table->getTable()]];
        if ($namespace = $this->getConfig('namespace')) {
            $common += compact('namespace');
        }

        $tagsTable = $this->_table->{$this->getConfig('tagsAlias')};
        $pk = $tagsTable->getPrimaryKey();
        $df = $tagsTable->getDisplayField();

        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (empty($tag)) {
                continue;
            }
            $tagKey = $this->_getTagKey($tag);
            $existingTag = $this->_tagExists($tagKey);
            if (!empty($existingTag)) {
                $result[] = $common + ['id' => $existingTag];
                continue;
            }
            list($id, $label) = $this->_normalizeTag($tag);
            $result[] = $common + compact(empty($id) ? $df : $pk) + [
                'tag_key' => $tagKey
            ];
        }

        return $result;
    }

    /**
     * Generates the unique tag key.
     *
     * @param string $tag Tag label.
     * @return string
     */
    protected function _getTagKey($tag)
    {
        return strtolower(Text::slug($tag));
    }

    /**
     * Checks if a tag already exists and returns the id if yes.
     *
     * @param string $tag Tag key.
     * @return null|int
     */
    protected function _tagExists($tag)
    {
        $tagsTable = $this->_table->{$this->getConfig('tagsAlias')}->getTarget();
        $result = $tagsTable->find()
            ->where([
                $tagsTable->aliasField('tag_key') => $tag,
            ])
            ->select([
                $tagsTable->aliasField($tagsTable->getPrimaryKey())
            ])
            ->first();

        if (!empty($result)) {
            return $result->id;
        }

        return null;
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
        $namespace = null;
        $label = $tag;
        $separator = $this->getConfig('separator');
        if (strpos($tag, $separator) !== false) {
            list($namespace, $label) = explode($separator, $tag);
        }

        return [
            trim($namespace),
            trim($label)
        ];
    }
}
