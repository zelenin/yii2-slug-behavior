<?php

namespace Zelenin\yii\behaviors\Service;

use Zelenin\Ddd\String\Domain\Model\TransformerCollection;
use Zelenin\Ddd\String\Infrastructure\Service\Transformer;
use Zelenin\Ddd\String\Infrastructure\Service\Transformer\IntlTransliterateTransformer;
use Zelenin\Ddd\String\Infrastructure\Service\Transformer\UrlifyTransformer;

class Slugifier
{
    /**
     * @var Transformer
     */
    private $transformer;

    /**
     * @param string $transliterateOptions
     * @param string $replacement
     * @param bool $lowercase
     */
    public function __construct($transliterateOptions, $replacement, $lowercase)
    {
        $this->transformer = new Transformer(new TransformerCollection([
            new IntlTransliterateTransformer($transliterateOptions),
            new UrlifyTransformer($replacement, $lowercase)
        ]));
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function slugify($string)
    {
        return $this->transformer->transform($string);
    }
}
