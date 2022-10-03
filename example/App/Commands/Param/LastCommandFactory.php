<?php

namespace App\Commands\Param;

use App\Interfaces\FactoryInterface;

interface LastCommandFactory extends FactoryInterface
{
	public function create(): LastCommand;
}
