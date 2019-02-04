<?php

namespace WebArch\BitrixExceptionLogger;

use Bitrix\Main\Diag\ExceptionHandlerLog;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionLogger extends ExceptionHandlerLog implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const OPTION_LOGGER = 'logger';

    const OPTION_TYPES = 'types';

    private $logTypeFlags;

    /**
     * ExceptionLogger constructor.
     */
    public function __construct()
    {
        /**
         * По-умолчанию логируется всё, кроме LOW_PRIORITY_ERROR.
         * Этот тип ошибки засоряет логи и появляется не только часто,
         * но и происходит от ошибок в коде ядра Битрикс.
         */
        $this->logTypeFlags = [
            self::UNCAUGHT_EXCEPTION => true,
            self::CAUGHT_EXCEPTION   => true,
            self::ASSERTION          => true,
            self::FATAL              => true,
        ];
    }

    /**
     * @param array $options
     */
    public function initialize(array $options)
    {
        $this->initLogger($options);
        $this->initTypes($options);
    }

    /**
     * @param array $options
     */
    private function initLogger(array $options)
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
    private function initTypes($options)
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
     * @param int $logType
     */
    public function write($exception, $logType)
    {
        if (
            !array_key_exists($logType, $this->logTypeFlags)
            || true !== $this->logTypeFlags[$logType]
        ) {
            return;
        }

        $this->logger->critical(
            sprintf(
                "%s [%s] %s (%s)",
                static::logTypeToString($logType),
                get_class($exception),
                $exception->getMessage(),
                $exception->getCode()
            ),
            ['stackTrace' => $exception->getTraceAsString()]
        );
    }

}
