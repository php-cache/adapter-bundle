<?php

namespace Cache\AdapterBundle\Exception;

/**
 * If you can connect to the cache storage you will get this exception thrown at you.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConnectException extends \RuntimeException
{
}