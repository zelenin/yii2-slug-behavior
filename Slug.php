<?php

namespace Zelenin\yii\behaviors;

use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\SluggableBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\validators\UniqueValidator;
use Zelenin\Slugifier\Slugifier;

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

                $slugParts = [];
                foreach ($attributes as $attribute) {
                    $slugParts[] = ArrayHelper::getValue($this->owner, $attribute);
                }
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
        $slugifier = new Slugifier($string);
        if ($this->transliterateOptions === null) {
            $slugifier->transliterateOptions = $this->transliterateOptions;
        }
        $slugifier->replacement = $replacement;
        $slugifier->lowercase = $lowercase;
        return $slugifier->getSlug();
    }

    /**
     * @param string $slug
     *
     * @return bool
     *
     * @throws InvalidConfigException
     */
    private function validateSlug($slug)
    {
        $validator = Yii::createObject(array_merge(['class' => UniqueValidator::className()], $this->uniqueValidator));

        /** @var ActiveRecord $model */
        $model = clone $this->owner;
        $model->clearErrors();
        $model->{$this->slugAttribute} = $slug;

        $validator->validateAttribute($model, $this->slugAttribute);
        return !$model->hasErrors();
    }

    /**
     * @param string $baseSlug
     * @param int $iteration
     *
     * @return string
     */
    private function generateUniqueSlug($baseSlug, $iteration)
    {
        return is_callable($this->uniqueSlugGenerator)
            ? call_user_func($this->uniqueSlugGenerator, $baseSlug, $iteration, $this->owner)
            : $baseSlug . $this->replacement . ($iteration + 1);
    }
}
