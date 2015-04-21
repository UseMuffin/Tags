<?php
namespace Muffin\Tags\Model\Entity;

use Cake\Datasource\EntityInterface;

interface TaggableInterface implements EntityInterface
{
    /**
     * Tag method.
     *
     * @param array $tags Array of tags.
     * @return bool
     */
    public function tag($tags);

    /**
     * Untag methods.
     *
     * @param mixed $tags Mixed tags.
     * @return bool
     */
    public function untag($tags);
}
