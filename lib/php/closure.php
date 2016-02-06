<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 1/3/16
 * Time: 11:22 AM
 */
/**
 * Converts any valid PHP callable into a Closure. Requires PHP 5.4.0+.
 *
 * The ramifications of this are many, but basically it means that any function
 * or method can be converted into a Closure, bound to another scope, and
 * executed easily. Works properly even with private methods.
 *
 * - On success, returns a Closure corresponding to the provided callable.
 * - If the parameter is not callable, issues an E_USER_WARNING and returns a
 *   Closure which only returns null.
 * - In the event of a strange or unrecoverable situation (e.g. providing a
 *   non-static method without an object), an UnexpectedValueException is
 *   thrown.
 *
 * @author Matthew Lanigan <rintaun@gmail.com>
 * @copyright (c) 2012, Matthew Lanigan
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link https://gist.github.com/2773168 Official closurize() gist
 * @param callable $callable
 * @return \Closure
 * @throws \UnexpectedValueException
 */
function closurize($callable) {
    if ($callable instanceof \Closure) {
        return $callable;
    }
    $is_callable = function($callable) {
        return \is_callable($callable);
    };
    $error = function() {
        $debug = \debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $fmt = 'Parameter 1 for closurize() must be callable ' .
            'in %s on line %d (issued at %s on line %d)';
        $error = \sprintf($fmt, $debug[1]['file'], $debug[1]['line'],
            $debug[0]['file'], $debug[0]['line']);
        \trigger_error($error, \E_USER_WARNING);
        return function() {
            return null;
        };
    };
    $object = null;
    $class  = null;
    $debug = \debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    if (isset($debug[1]['object']) && \is_object($debug[1]['object'])) {
        $object = $debug[1]['object'];
        $class  = $debug[1]['class'];
        $is_callable = $is_callable->bindTo($object, $object);
    }
    if (!$is_callable($callable)) {
        if (isset($callable[0]) && is_object($callable[0])) {
            $is_callable = $is_callable->bindTo($callable[0], $callable[0]);
        } else if (isset($callable[0]) && \class_exists($callable[0])) {
            $is_callable = $is_callable->bindTo(null, $callable[0]);
        }
        if (!$is_callable($callable)) {
            return $error();
        }
    }
    if (\is_string($callable) && (\strpos($callable, '::') === false)) {
        $ref = new \ReflectionFunction($callable);
        return $ref->getClosure();
    } else if (\is_string($callable)) {
        $callable = \explode('::', $callable);
    }
    if (!\is_array($callable)) {
        throw new \UnexpectedValueException('Callable is not string, array, '.
            'or Closure');
    }
    if (\is_object($callable[0])) {
        $ref = new \ReflectionMethod($callable[0], $callable[1]);
        return $ref->getClosure($callable[0]);
    }
    if (!\is_string($callable[0])) {
        throw new \UnexpectedValueException('Callable class is not string ' .
            'or object');
    }
    switch ($callable[0]) {
        case 'self':
            if (!\is_object($object) && \is_null($class)) {
                return $error();
            }
            $self = function() {
                return \get_class();
            };
            $self = $self->bindTo($object, $class);
            $ref = new \ReflectionMethod($self(), $callable[1]);
            $callable[0] = $object;
            break;
        case 'static':
            if (!\is_object($object)) {
                return $error();
            }
            $static = function() {
                return \get_called_class();
            };
            $static = $static->bindTo($object, $class);
            $ref = new \ReflectionMethod($static(), $callable[1]);
            $callable[0] = $object;
            break;
        case 'parent':
            if (!\is_object($object)) {
                return $error();
            }
            $parent = function() {
                return \get_parent_class();
            };
            $parent = $parent->bindTo($object, $class);
            $ref = new \ReflectionMethod($parent(), $callable[1]);
            $callable[0] = $object;
            break;
        default:
            $ref = new \ReflectionMethod($callable[0], $callable[1]);
            break;
    }
    if (!$ref->isStatic() && \is_object($callable[0])) {
        return $ref->getClosure($callable[0]);
    } else if (!$ref->isStatic()) {
        throw new \UnexpectedValueException('Callable method is not static, ' .
            'but no calling object available');
    }
    return $ref->getClosure();
}