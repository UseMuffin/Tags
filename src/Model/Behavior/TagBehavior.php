<?php
declare(strict_types=1);

namespace Muffin\Tags\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use InvalidArgumentException;
use RuntimeException;

/**
 * Tag Behavior
 */
class TagBehavior extends Behavior
{
    /**
     * Configuration Options
     * - delimiter: The delimiter used to explode() the tags. Default is comma.
     * - separator: Namespace separator, by default semicolon.
     * - identifier: The value of the field that is used to associate a tagged
     *   entry with a specific model. This is required if you use int vs UUID
     *   ids to get an unique composite of the tag id, model and model id. If
     *   not specified the behavior will use the table name by default.
     * - fieldMap: An array that maps fields the behavior is using to the fields
     *   in the tags and tagged tables.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'delimiter' => ',',
        'separator' => ':',
        'namespace' => null,
        // Tags table setup
        'tagsAlias' => 'Tags',
        'tagsAssoc' => [
            'className' => 'Muffin/Tags.Tags',
            'joinTable' => 'tags_tagged',
            'foreignKey' => 'fk_id',
            'targetForeignKey' => 'tag_id',
            'propertyName' => 'tags',
        ],
        // Tagged table setup
        'taggedAlias' => 'Tagged',
        'taggedAssoc' => [
            'className' => 'Muffin/Tags.Tagged',
        ],
        // Tags counter setup
        'taggedCounter' => ['tag_count' => [
            'conditions' => []
        ]],
        'tagsCounter' => [
            'counter'
        ],
        // Events
        'implementedEvents' => [
            'Model.beforeMarshal' => 'beforeMarshal',
        ],
        // Implemented methods
        'implementedMethods' => [
            'normalizeTags' => 'normalizeTags',
            'tagExists' => 'tagExists'
        ],
        'identifier' => null,
        // Allows you to map any of the fields we use to whatever is the
        // fieldname in your table
        'fieldMap' => [
            'tag_label' => 'label',
            'tag_namespace' => 'namespace',
            'tag_key' => 'tag_key',
            'tagged_model' => 'fk_table'
        ]
    ];

    /**
     * Gets a field name from the field map
     *
     * @param $field Field name
     * @return string
     */
    protected function getField(string $field): string
    {
        $field = $this->getConfig('fieldMap.' . $field);
        if (empty($field)) {
            throw new InvalidArgumentException(sprintf(
                'There is no field mapped to `%s`',
                $field
            ));
        }

        return $field;
    }

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

        $table = $this->getTable();
        $tableClass = get_class($table);
        $tableAlias = $this->getTable()->getAlias();

        $assocConditions = [$taggedAlias . '.' . $this->getField('tagged_model') => $this->getIdentifier()];

        if (!$table->hasAssociation($taggedAlias)) {
            $table->hasMany($taggedAlias, $taggedAssoc + [
                    'foreignKey' => $tagsAssoc['foreignKey'],
                    'conditions' => $assocConditions,
                ]);
        }

        if (!$table->hasAssociation($tagsAlias)) {
            $table->belongsToMany($tagsAlias, $tagsAssoc + [
                    'through' => $table->{$taggedAlias}->getTarget(),
                    'conditions' => $assocConditions,
                ]);
        }

        if (!$table->{$tagsAlias}->hasAssociation($tableAlias)) {
            $table->{$tagsAlias}
                ->belongsToMany($tableAlias, [
                        'className' => $tableClass,
                        'targetTable' => $table,
                    ] + $tagsAssoc);
        }

        if (!$table->{$taggedAlias}->hasAssociation($tableAlias)) {
            $table->{$taggedAlias}
                ->belongsTo($tableAlias, [
                    'className' => $tableClass,
                    'targetTable' => $table,
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

        $taggedTable = $this->getTable()->{$taggedAlias};

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
            if (!$this->getTable()->hasField($field)) {
                throw new RuntimeException(sprintf(
                    'Field `%s` does not exist in table `%s`',
                    $field,
                    $this->getTable()->getTable()
                ));
            }
        }

        if (!$counterCache->getConfig($taggedAlias)) {
            $field = key($config['taggedCounter']);
            $config['taggedCounter']['tag_count']['conditions'] = [
                $taggedTable->aliasField($this->getField('tagged_model')) => $this->getIdentifier()
            ];
            $counterCache->setConfig($this->getTable()->getAlias(), $config['taggedCounter']);
        }
    }

    /**
     * Gets the identifier for the model
     *
     * @return string
     */
    protected function getIdentifier(): string
    {
        $identifier = $this->getConfig('identifier');
        if (empty($identifier)) {
            return $this->getTable()->getTable();
        }

        return $identifier;
    }

    /**
     * Normalizes tags.
     *
     * @param array|string $tags List of tags as an array or a delimited string (comma by default).
     * @return array Normalized tags valid to be marshaled.
     */
    public function normalizeTags($tags): array
    {
        if (is_string($tags)) {
            $tags = explode($this->getConfig('delimiter'), $tags);
        }

        $common = ['_joinData' => [$this->getField('tagged_model') => $this->getIdentifier()]];
        if ($namespace = $this->getConfig('namespace')) {
            $common += compact('namespace');
        }

        $tagsTable = $this->getTable()->{$this->getConfig('tagsAlias')};
        $pk = $tagsTable->getPrimaryKey();
        $displayField = $tagsTable->getDisplayField();
        $result = [];

        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (empty($tag)) {
                continue;
            }

            $tagKey = $this->generateTagKey($tag);
            $existingTag = $this->tagExists($tagKey);

            if (!empty($existingTag)) {
                $result[] = $common + [$tagsTable->getPrimaryKey() => $existingTag];
                continue;
            }

            list($id, $label) = $this->normalizeTag($tag);
            $result[] = $common + compact(empty($id) ? $displayField : $pk) + [
                    $this->getField('tag_key') => $tagKey,
                    $this->getField('tag_label') => $label,
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
    public function generateTagKey($tag): string
    {
        return strtolower(Text::slug($tag));
    }

    /**
     * Checks if a tag already exists and returns the id if yes.
     *
     * @param string $tag Tag key.
     * @return null|string|int
     */
    public function tagExists($tag)
    {
        $tagsTable = $this->getTable()->{$this->getConfig('tagsAlias')}->getTarget();
        $result = $tagsTable->find()
            ->where([
                $tagsTable->aliasField($this->getField('tag_key')) => $tag,
            ])
            ->select([
                $tagsTable->aliasField($tagsTable->getPrimaryKey())
            ])
            ->first();

        if (!empty($result)) {
            return $result->get($tagsTable->getPrimaryKey());
        }

        return null;
    }

    /**
     * Normalizes a tag string by trimming unnecessary whitespace and extracting the tag identifier
     * from a tag in case it exists.
     *
     * @param string $tag Tag.
     * @return array The tag's namespace and label.
     */
    public function normalizeTag($tag)
    {
        $namespace = null;
        $label = $tag;
        $separator = $this->getConfig('separator');

        if (strpos($tag, $separator) !== false) {
            list($namespace, $label) = explode($separator, $tag);
        }

        return [
            trim((string)$namespace),
            trim($label)
        ];
    }
}
