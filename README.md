Адаптер PSR-3 логгера для логирования исключений в Битрикс. 


Как использовать: 
-----------------

1 Установить через composer 

`composer require webarchitect609/bitrix-exception-logger`

2 Подключить автозагрузчик composer в init.php

```php
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/vendor/autoload.php';
```

3 Подключить логирование исключений в .settings.php в ключе 
[`exception_handling`](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=2795&LESSON_PATH=3913.5062.2795#exception_handling). 
(В примере ниже используется 
[Monolog](https://packagist.org/packages/monolog/monolog), который следует предварительно установить 
`composer require monolog/monolog`)

```php
[
    //...
    'exception_handling' =>
            [
                'value'    =>
                    [
                        'debug' => false,
                        'handled_errors_types'       => E_ERROR
                            | E_PARSE
                            | E_CORE_ERROR
                            | E_COMPILE_ERROR
                            | E_USER_ERROR
                            | E_RECOVERABLE_ERROR,
                        'exception_errors_types'     => E_ERROR
                            | E_PARSE
                            | E_CORE_ERROR
                            | E_COMPILE_ERROR
                            | E_USER_ERROR
                            | E_RECOVERABLE_ERROR,
                        'ignore_silence'             => false,
                        'assertion_throws_exception' => true,
                        'assertion_error_type'       => E_USER_ERROR,
                        'log'                        => [
                            'class_name' => \WebArch\BitrixExceptionLogger\ExceptionLogger::class,
                            'settings'   => [
                                'logger' => new \Monolog\Logger(
                                                'BX_EXPN',
                                                new \Monolog\Handler\StreamHandler(
                                                    '/var/log/www_exception.log', \Psr\Log\LogLevel::INFO
                                                )
                                            ),
                                'types' => [
                                               \Bitrix\Main\Diag\ExceptionHandlerLog::UNCAUGHT_EXCEPTION,
                                               \Bitrix\Main\Diag\ExceptionHandlerLog::IGNORED_ERROR,
                                               \Bitrix\Main\Diag\ExceptionHandlerLog::FATAL,
                                           ]
                            ],
                        ],
                    ],
                'readonly' => true,
            ],
    //...
]
```
