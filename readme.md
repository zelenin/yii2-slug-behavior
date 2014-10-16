# Yii2 slug behavior

[Yii2](http://www.yiiframework.com) slug behavior

## Installation

### Composer

The preferred way to install this extension is through [Composer](http://getcomposer.org/).

Either run ```php composer.phar require zelenin/yii2-slug-behavior "0.2.*"```

or add ```"zelenin/yii2-slug-behavior": "0.2.*"``` to the require section of your ```composer.json```

### Using

Attach the behavior in your model:

```php
public function behaviors()
{
    return [
        'slug' => [
            'class' => 'Zelenin\yii\behaviors\Slug',
            'slugAttribute' => 'slug',
            'attribute' => 'name',
            // optional params (default values)
            'ensureUnique' => true,
            'translit' => true,
            'replacement' => '-',
            'lowercase' => true,

        ]
    ];
}
```

Info: attribute names was changed on 0.2.0 version.

Slug may be generated from multiple and related attributes:

```php
public function behaviors()
{
    return [
        'slug' => [
            ...
            'attribute' => ['name', 'language.username'],
            ...
        ]
    ];
}
```

## Author

[Aleksandr Zelenin](https://github.com/zelenin/), e-mail: [aleksandr@zelenin.me](mailto:aleksandr@zelenin.me)
