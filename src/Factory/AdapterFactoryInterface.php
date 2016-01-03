<?php

namespace Cache\AdapterBundle\Factory;

interface AdapterFactoryInterface
{
    public function createAdapter(array $options = array());
}