<?php

namespace PQL\Tests\InputData;

class TestWhereDualCondition implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 6, "comments.text" => "wdw2015", "comments.rok" => 2015, "comments.userId" => 1, ],
			["comments.id" => 8, "comments.text" => "awd2015", "comments.rok" => 2015, "comments.userId" => 1, ],
			["comments.id" => 11, "comments.text" => "awdawdqweawd2015", "comments.rok" => 2015, "comments.userId" => 1, ],
		];
	}
}
