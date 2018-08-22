<?php
declare(strict_types=1);

namespace Muffin\Tags\Model\Table;

use Cake\Core\Plugin;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;

/**
 * TagsTable
 */
class TagsTable extends Table
{
    /**
     * Initialize table config.
     *
     * @param array $config Config options
     * @return void
     */
    public function initialize(array $config)
    {
        $this->setTable('tags_tags');
        $this->setDisplayField('label');

        $this->addBehavior('Timestamp');
        if (Plugin::loaded('Muffin/Slug')) {
            $this->addBehavior('Muffin/Slug.Slug');
        }
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->isUnique(['tag_key'], $this);

        return $rules;
    }
}
