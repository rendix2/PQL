<?php

namespace PQL\Tests\InputData;

class TestWhereGreaterInc implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 2, "comments.text" => "wda2021", "comments.rok" => 2021, "comments.userId" => 2, ],
			["comments.id" => 5, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 500, ],
			["comments.id" => 9, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 1, ],
			["comments.id" => 10, "comments.text" => "awdawddd2020", "comments.rok" => 2020, "comments.userId" => 1, ],
		];
	}
}
