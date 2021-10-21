<?php

namespace PQL\Tests\InputData;

class TestLeftJoinTableOnConditionNotInArray implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 3, "comments.text" => "wad2015", "comments.rok" => 2015, "comments.userId" => 3, "User.id" => null, "User.username" => null, ],
			["comments.id" => 4, "comments.text" => "awd2018", "comments.rok" => 2018, "comments.userId" => 20, "User.id" => null, "User.username" => null, ],
			["comments.id" => 5, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 500, "User.id" => null, "User.username" => null, ],
			["comments.id" => 2, "comments.text" => "wda2021", "comments.rok" => 2021, "comments.userId" => 2, "User.id" => 2, "User.username" => "xpy2", ],
		];
	}
}
