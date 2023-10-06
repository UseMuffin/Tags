<?php
declare(strict_types=1);

namespace Muffin\Tags\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Text;

class Tag extends Entity
{
    /**
     * List of properties that can be mass assigned.
     *
     * @var array
     */
    public $accessible = [
        '*' => false,
        'label' => true,
    ];

    /**
     * Template used by `__toString()`. If empty, fallsback to `EntityTrait::__toString()`.
     *
     * @var string
     */
    protected $_toStringTemplate = ':label';

    /**
     * Getter/setter for the `$_toStringTemplate` property.
     *
     * @param string $template String template. Supported placeholders are `:label` and `:id`.
     * @return string
     */
    public function toStringTemplate($template = null)
    {
        if ($template !== null) {
            $this->_toStringTemplate = $template;
        }
        return $this->_toStringTemplate;
    }

    /**
     * Returns the tag as a string (according to the `$_toStringTemplate` defined) or fallsback
     * to the `EntityTrait::__toString()`.
     *
     * @return string
     */
    public function __toString()
    {
        return !empty($this->_toStringTemplate) ?
            Text::insert($this->toStringTemplate(), $this->toArray()) :
            parent::__toString();
    }
}
