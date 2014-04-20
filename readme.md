# Yii2 slug behavior

[Yii2](http://www.yiiframework.com) slug behavior

## Installation

### Composer

The preferred way to install this extension is through [Composer](http://getcomposer.org/).

Either run

	php composer.phar require zelenin/yii2-slug-behavior "dev-master"

or add

	"zelenin/yii2-slug-behavior": "dev-master"

to the require section of your composer.json

### Using

Attach the behavior in your model:

```php
public function behaviors()
{
    return [
        'slug' => [
            'class' => 'Zelenin\yii\behaviors\Slug',
            'source_attribute' => 'name',
            'slug_attribute' => 'slug',

            // optional params
            'translit' => true,
            'replacement' => '-',
            'lowercase' => true,
            'unique' => true
        ]
    ];
}
```

Slug may be generated from multiple and related attributes:

```php
public function behaviors()
{
    return [
        'slug' => [
            'class' => 'Zelenin\yii\behaviors\Slug',
            'source_attribute' => [
                'name',
                'language.name'
            ],
            'slug_attribute' => 'slug',

            // optional params
            'translit' => true,
            'replacement' => '-',
            'lowercase' => true,
            'unique' => true
        ]
    ];
}
```

## Author

[Aleksandr Zelenin](https://github.com/zelenin/), e-mail: [aleksandr@zelenin.me](mailto:aleksandr@zelenin.me)
