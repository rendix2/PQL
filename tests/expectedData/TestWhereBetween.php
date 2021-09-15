<?php

namespace PQL\Tests\InputData;

class TestWhereBetween implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 4, "comments.text" => "awd2018", "comments.rok" => 2018, "comments.userId" => 20, ],
		];
	}
}
