#Yii2 helpers library
##Description
Yii2 helpers library which used in web-application development.



##Composition
###ReflectionHelper
Class for work with classes, functions etc. using Reflection 

###DateTimeHelper
Class for work with Date and Time

###RequestHelper
Class for work with URL and requests

###Dumper
More effective dump

Usage: 

```php
// smart var dump
\Yii::$app->dump->log($this->findCondition);
```
```php
// smart var dump with die
\Yii::$app->dump->stop($this->findCondition);
```
```php
// smart var dump several variables
\Yii::$app->dump->arr($this->findCondition1, $this->findCondition2);
```

###Singleton
Class Singleton. Parent for create own simple singleton

###Multiton
Class Multiton. Parent for create own simple multiton