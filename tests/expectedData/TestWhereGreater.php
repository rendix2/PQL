<?php

namespace PQL\Tests\InputData;

class TestWhereGreater implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 2, "comments.text" => "wda2021", "comments.rok" => 2021, "comments.userId" => 2, ],
		];
	}
}
