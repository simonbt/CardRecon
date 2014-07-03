<?php
/**
 * KLogger description
 * 
 * @author Thomas Gray <thomas.gray@randomstorm.com>
 * @package 
 * @subpackage 
 */

namespace StormFramework\Logger;

use JsonSerializable, InvalidArgumentException, RuntimeException;


/**
 * @description Class ContextLogger is written as an extension to the standard KLogger
 * it adds options to allow the logger to log more than just the date, level and a
 * message, it adds so called "context" to the message.
 *
 * In example, a standard log line might look like this:
 *
 * [2014-01-01 00:00:00] [FATAL] {Message goes here}
 *
 * With the Context logger, we can include variables that are part of the running
 * state of our application, to help provide those debugging these logs in the future
 * with more information about what was happening.
 *
 * For example, for a web application you might want to set the users session id and
 * their current request url, and their request type, you might end up with a line
 * like this:
 *
 * [2014-01-01 00:00:00] [sessionId:12345678] [requestUrl:/users/logout.php] [requestType:GET] [FATAL] {The message}
 *
 * That added context can be invaluable in debugging an application after the fact.
 *
 *
 * @package Katzgrau\KLogger
 * @author Thomas Gray <thomas.gray@randomstorm.com>
 *
 */
class ContextLogger extends Logger
{
    /**
     * @var array Components that define the context in which this log line is written
     */
    protected $context = array();

    /**
     * Accepts a string key and a mixed value. Will convert objects implementing JsonSerializable, __toString or toArray
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function addContext($key, $value)
    {
        if(!is_string($key) || strlen($key) < 1)
        {
            throw new InvalidArgumentException('You must provide me with a key of a > 0 length string');
        }

        $value = $this->sanitizeValue($value);

        $this->context[$key] = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function formatMessage($level, $message, $context)
    {
        $level = strtoupper($level);
        if(!empty($context))
        {
            $message .= PHP_EOL . $this->indent($this->contextToString($context));
        }

        return $this->fetchStringContext($level) . '{' . $message . '}' . PHP_EOL;
    }

    /**
     * Formats all context set inside me, into a nice pretty string.
     * @param string $level
     * @return string
     */
    protected function fetchStringContext($level)
    {
        $outputString = '[' . $this->getTimestamp() . '] [' . $level . '] ';

        foreach($this->context as $key => $value)
        {
            $outputString .= '[' . trim($key) . ':' . $value . '] ';
        }

        return $outputString;
    }

    /**
     * Removes an item of context previously added, fails quietly, so does not notify on removing missing context.
     *
     * @param string $key
     * @return $this
     */
    public function removeContext($key)
    {
        if(array_key_exists($key, $this->context))
        {
            unset($this->context[$key]);
        }

        return $this;
    }

    /**
     * Returns boll statement to see if context key has already been set.
     *
     * @param string $key
     * @return bool
     */
    public function hasContext($key)
    {
        return array_key_exists($key, $this->context);
    }

    /**
     * Method accepts a mixed value and returns a string version of it, throwing a RuntimeException if there's a problem8
     *
     * @param mixed $value
     * @return string
     */
    protected function sanitizeValue($value)
    {
        $stringValue = $this->convertValueToString($value);

        $stringValue = str_replace(
            array(
                '[',
                ']',
            ),
            array(
                '\[',
                '\]',
            ),
            $stringValue
        );

        return $stringValue;
    }

    /**
     * Accepts a mixed value and will convert said value into some kind of well formatted string.
     *
     * @param mixed $value
     * @return mixed|string
     * @throws \RuntimeException
     */
    protected function convertValueToString($value)
    {
        switch(gettype($value))
        {
            case "object":
                return $this->convertObjectToString($value);

            case "integer":
            case "double":
                return $value;

            case "boolean":
                return ($value ? 'true' : 'false');

            case "NULL":
                return 'null';

            case "array":
                return json_encode($value);

            case "string":
                return $value;

            default:
                throw new RuntimeException('I cannot accept a ' . gettype($value) . ' as context.');
        }
    }

    /**
     * Accepts an object as an arg, and if it implements __toString, toArray or JsonSerializable it will convert it
     * @param object $value
     * @return mixed|string
     */
    protected function convertObjectToString($value)
    {
        if(method_exists($value, '__toString'))
        {
            return (string) $value;
        }

        if($value instanceof JsonSerializable)
        {
            return $value->jsonSerialize();
        }

        if(method_exists($value, 'toArray'))
        {
            return json_encode($value->toArray());
        }

        throw new RuntimeException(
            __METHOD__ . ' cannot accept the value you passed, it MUST be an object with'
            . ' sensible __toString magic, JsonSerializable implementation or a toArray method.'
        );
    }

} 
