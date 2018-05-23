<?php
namespace Muffin\Tags\Model\Entity;

use Cake\Collection\Collection;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * TagAwareTrait
 */
trait TagAwareTrait
{

    /**
     * Tag entity with given tags.
     *
     * @param string|array $tags List of tags as an array or a delimited string (comma by default).
     * @param bool $merge Whether to merge or replace tags. Default true.
     * @return bool|\Cake\ORM\Entity False on failure, entity on success.
     */
    public function tag($tags, $merge = true)
    {
        return $this->_updateTags($tags, $merge ? 'append' : 'replace');
    }

    /**
     * Untag entity from given tags.
     *
     * @param string|array $tags List of tags as an array or a delimited string (comma by default).
     *   If no value is passed all tags will be removed.
     * @return bool|\Cake\ORM\Entity False on failure, entity on success.
     */
    public function untag($tags = null)
    {
        if (empty($tags)) {
            return $this->_updateTags([], 'replace');
        }

        $table = TableRegistry::get($this->source());
        $behavior = $table->behaviors()->Tag;
        $assoc = $table->getAssociation($behavior->getConfig('tagsAlias'));
        $property = $assoc->getProperty();
        $id = $this->get($table->getPrimaryKey());
        $untags = $behavior->normalizeTags($tags);

        if (!$tags = $this->get($property)) {
            $contain = [$behavior->getConfig('tagsAlias')];
            $tags = $table->get($id, compact('contain'))->get($property);
        }

        $tagsTable = $table->{$behavior->getConfig('tagsAlias')};
        $pk = $tagsTable->getPrimaryKey();
        $df = $tagsTable->getDisplayField();

        foreach ($tags as $k => $tag) {
            $tags[$k] = [
                $pk => $tag->{$pk},
                $df => $tag->{$df},
            ];
        }

        foreach ($untags as $untag) {
            foreach ($tags as $k => $tag) {
                if ((empty($untag[$pk]) || $tag[$pk] === $untag[$pk]) &&
                    (empty($untag[$df]) || $tag[$df] === $untag[$df])
                ) {
                    unset($tags[$k]);
                }
            }
        }

        return $this->_updateTags(
            array_map(
                function ($i) {
                    return implode(':', $i);
                },
                $tags
            ),
            'replace'
        );
    }

    /**
     * Tag entity with given tags.
     *
     * @param string|array $tags List of tags as an array or a delimited string (comma by default).
     * @param string $saveStrategy Whether to merge or replace tags.
     *   Valid values 'append', 'replace'.
     * @return bool|\Cake\ORM\Entity False on failure, entity on success.
     */
    protected function _updateTags($tags, $saveStrategy)
    {
        $table = TableRegistry::get($this->source());
        $behavior = $table->behaviors()->Tag;
        $assoc = $table->getAssociation($behavior->getConfig('tagsAlias'));
        $resetStrategy = $assoc->getSaveStrategy();
        $assoc->setSaveStrategy($saveStrategy);
        $table->patchEntity($this, [$assoc->getProperty() => $tags]);
        $result = $table->save($this);
        $assoc->setSaveStrategy($resetStrategy);

        return $result;
    }
}
