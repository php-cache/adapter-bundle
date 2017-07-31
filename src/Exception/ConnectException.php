<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\Exception;

/**
 * If you can connect to the cache storage you will get this exception thrown at you.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConnectException extends \RuntimeException
{
}
