<?php
namespace Muffin\Tags\Model\Entity;

use Cake\Collection\Collection;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

trait TagAwareTrait
{

    /**
     * Tag entity with given tags.
     *
     * @param string|array $tags List of tags as an array or a delimited string (comma by default).
     * @param boolean $merge Whether to merge or replace tags. Default true.
     * @return boolean|\Cake\ORM\Entity False on failure, entity on success.
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
     * @return boolean|\Cake\ORM\Entity False on failure, entity on success.
     */
    public function untag($tags = null)
    {
        if (empty($tags)) {
            return $this->_updateTags([], 'replace');
        }

        $table = TableRegistry::get($this->source());
        $behavior = $table->behaviors()->Tag;
        $assoc = $table->association($behavior->config('tagsAlias'));
        $property = $assoc->property();
        $id = $this->get($table->primaryKey());
        $untags = $behavior->normalizeTags($tags);

        if (!$tags = $this->get($property)) {
            $contain = [$behavior->config('tagsAlias')];
            $tags = $table->get($id, compact('contain'))->get($property);
        }

        $tagsTable = $table->{$behavior->config('tagsAlias')};
        $pk = $tagsTable->primaryKey();
        $df = $tagsTable->displayField();

        foreach ($tags as $k => $tag) {
            $tags[$k] = [
                $pk => $tag->{$pk},
                $df => $tag->{$df},
            ];
        }

        foreach ($untags as $untag) {
            foreach ($tags as $k => $tag) {
                if (
                    (empty($untag[$pk]) || $tag[$pk] === $untag[$pk]) &&
                    (empty($untag[$df]) || $tag[$df] === $untag[$df])
                ) {
                    unset($tags[$k]);
                }
            }
        }

        return $this->_updateTags(
            array_map(function ($i) { return implode(':', $i); }, $tags),
            'replace'
        );
    }

    /**
     * Tag entity with given tags.
     *
     * @param string|array $tags List of tags as an array or a delimited string (comma by default).
     * @param string $saveStrategy Whether to merge or replace tags.
     *   Valid values 'append', 'replace'.
     * @return boolean|\Cake\ORM\Entity False on failure, entity on success.
     */
    protected function _updateTags($tags, $saveStrategy)
    {
        $table = TableRegistry::get($this->source());
        $behavior = $table->behaviors()->Tag;
        $assoc = $table->association($behavior->config('tagsAlias'));
        $resetStrategy = $assoc->saveStrategy();
        $assoc->saveStrategy($saveStrategy);
        $table->patchEntity($this, [$assoc->property() => $tags]);
        $result = $table->save($this);
        $assoc->saveStrategy($resetStrategy);
        return $result;
    }
}
