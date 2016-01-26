<?php

namespace Zelenin\yii\behaviors;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use Zelenin\yii\behaviors\Service\Slugifier;

class Slug extends SluggableBehavior
{
    /**
     * @var string
     */
    public $slugAttribute = 'slug';

    /**
     * @var string|array
     */
    public $attribute = 'name';

    /**
     * @var bool
     */
    public $ensureUnique = true;

    /**
     * @var string
     */
    public $replacement = '-';

    /**
     * @var bool
     */
    public $lowercase = true;

    /**
     * @var bool
     */
    public $immutable = true;

    /**
     * @var string
     * @link http://userguide.icu-project.org/transforms/general
     */
    public $transliterateOptions = null;

    /**
     * @var bool
     */
    private $slugIsEmpty = false;

    /**
     * @inheritdoc
     * @param ActiveRecord $owner
     */
    public function attach($owner)
    {
        $this->attribute = (array)$this->attribute;
        $primaryKey = $owner->primaryKey();
        $primaryKey = is_array($primaryKey) ? array_shift($primaryKey) : $primaryKey;
        if (in_array($primaryKey, $this->attribute, true) && $owner->getIsNewRecord()) {
            $this->attributes[ActiveRecord::EVENT_AFTER_INSERT] = $this->slugAttribute;
        }

        parent::attach($owner);
    }

    /**
     * @inheritdoc
     */
    protected function getValue($event)
    {
        /* @var $owner ActiveRecord */
        $owner = $this->owner;

        if (!empty($owner->{$this->slugAttribute}) && !$this->slugIsEmpty && $this->immutable) {
            $slug = $owner->{$this->slugAttribute};
        } else {
            if ($owner->getIsNewRecord()) {
                $this->slugIsEmpty = true;
            }
            if ($this->attribute !== null) {
                $attributes = $this->attribute;

                $slugParts = array_map(function ($attribute) {
                    return ArrayHelper::getValue($this->owner, $attribute);
                }, $attributes);

                $slug = $this->slugify(implode($this->replacement, $slugParts), $this->replacement, $this->lowercase);

                if (!$owner->getIsNewRecord() && $this->slugIsEmpty) {
                    $owner->{$this->slugAttribute} = $slug;
                    $owner->save(false, [$this->slugAttribute]);
                }
            } else {
                $slug = parent::getValue($event);
            }
        }

        if ($this->ensureUnique) {
            $baseSlug = $slug;
            $iteration = 0;
            while (!$this->validateSlug($slug)) {
                $iteration++;
                $slug = $this->generateUniqueSlug($baseSlug, $iteration);
            }
        }

        return $slug;
    }

    /**
     * @param string $string
     * @param string $replacement
     * @param bool $lowercase
     *
     * @return string
     */
    private function slugify($string, $replacement = '-', $lowercase = true)
    {
        $transliterateOptions = $this->transliterateOptions !== null ? $this->transliterateOptions : 'Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFKC;';
        return (new Slugifier($transliterateOptions, $replacement, $lowercase))->slugify($string);
    }

    /**
     * @param string $baseSlug
     * @param int $iteration
     *
     * @return string
     */
    protected function generateUniqueSlug($baseSlug, $iteration)
    {
        return is_callable($this->uniqueSlugGenerator)
            ? call_user_func($this->uniqueSlugGenerator, $baseSlug, $iteration, $this->owner)
            : $baseSlug . $this->replacement . ($iteration + 1);
    }
}
