<?php

namespace PQL\Tests\InputData;

class TestDistinctColumn implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.rok" => 2021, ],
			["comments.rok" => 2015, ],
			["comments.rok" => 2018, ],
			["comments.rok" => 2020, ],
		];
	}
}
