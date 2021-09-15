<?php

namespace PQL\Tests\InputData;

class TestInnerJoinTableOnCondition implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 6, "comments.text" => "wdw2015", "comments.rok" => 2015, "comments.userId" => 1, "User.id" => 1, "User.username" => "xpy", ],
			["comments.id" => 8, "comments.text" => "awd2015", "comments.rok" => 2015, "comments.userId" => 1, "User.id" => 1, "User.username" => "xpy", ],
			["comments.id" => 9, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 1, "User.id" => 1, "User.username" => "xpy", ],
			["comments.id" => 10, "comments.text" => "awdawddd2020", "comments.rok" => 2020, "comments.userId" => 1, "User.id" => 1, "User.username" => "xpy", ],
			["comments.id" => 11, "comments.text" => "awdawdqweawd2015", "comments.rok" => 2015, "comments.userId" => 1, "User.id" => 1, "User.username" => "xpy", ],
			["comments.id" => 2, "comments.text" => "wda2021", "comments.rok" => 2021, "comments.userId" => 2, "User.id" => 2, "User.username" => "xpy2", ],
		];
	}
}