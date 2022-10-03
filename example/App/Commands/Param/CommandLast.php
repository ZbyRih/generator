<?php

namespace App\Commands\Param;

use App\Abstractions\AbstractCommandRequest;
use App\Interfaces\RequestInterface;
use App\Models\Orm\Orm;

class LastCommand extends AbstractCommandRequest
{
	private LastRequest $request;

	public function __construct(
		private Orm $orm
	){
	}

	/** @param LastRequest $request */
	public function setRequest(RequestInterface $request): void
	{
		$this->request = $request;
	}

	public function execute(): void
	{
		$request = $this->request;

	}
}
