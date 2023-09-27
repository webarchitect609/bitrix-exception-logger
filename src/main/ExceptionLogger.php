<?php

namespace WebArch\BitrixExceptionLogger;

use Bitrix\Main\Diag\ExceptionHandlerLog;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;
use WebArch\LogTools\Traits\LogExceptionTrait;

class ExceptionLogger extends ExceptionHandlerLog implements LoggerAwareInterface
{
    use LogExceptionTrait;

    protected const OPTION_LOGGER = 'logger';

    protected const OPTION_TYPES = 'types';

    /**
     * @var array<int, bool>
     *
     * По-умолчанию логируется всё, кроме LOW_PRIORITY_ERROR.
     * Этот тип ошибки засоряет логи и появляется не только часто,
     * но и происходит от ошибок в коде ядра Битрикс.
     */
    private array $logTypeFlags = [
        self::UNCAUGHT_EXCEPTION => true,
        self::CAUGHT_EXCEPTION   => true,
        self::ASSERTION          => true,
        self::FATAL              => true,
    ];

    /**
     * @param array $options
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * */
    public function initialize(array $options)
    {
        $this->initLogger($options);
        $this->initTypes($options);
    }

    /**
     * @param array $options
     */
    private function initLogger(array $options): void
    {
        if (
            !array_key_exists(self::OPTION_LOGGER, $options)
            || !($options[self::OPTION_LOGGER] instanceof LoggerInterface)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Missing valid logger. Pass logger of type %s to `%s` option',
                    LoggerInterface::class,
                    self::OPTION_LOGGER
                )
            );
        }

        $this->setLogger($options[self::OPTION_LOGGER]);
    }

    /**
     * @param array $options
     */
    private function initTypes(array $options): void
    {
        if (!array_key_exists(self::OPTION_TYPES, $options) || !is_array($options[self::OPTION_TYPES])) {
            return;
        }

        $this->logTypeFlags = [];
        foreach ($options[self::OPTION_TYPES] as $logType) {
            if (is_int($logType)) {
                $this->logTypeFlags[$logType] = true;
            }
        }
    }

    /**
     * @param Throwable $exception
     * @param int       $logType
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function write($exception, $logType)
    {
        if (
            !array_key_exists($logType, $this->logTypeFlags)
            || true !== $this->logTypeFlags[$logType]
        ) {
            return;
        }

        $this->logException(
            $exception,
            LogLevel::CRITICAL,
            ['logType' => static::logTypeToString($logType)]
        );
    }
}
