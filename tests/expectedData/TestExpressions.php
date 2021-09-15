<?php

namespace PQL\Tests\InputData;

class TestExpressions implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 2, "comments.text" => "wda2021", "comments.rok" => 2021, "comments.userId" => 2, "1 + 2 + (3 - 4)" => 2.0, ],
			["comments.id" => 3, "comments.text" => "wad2015", "comments.rok" => 2015, "comments.userId" => 3, "1 + 2 + (3 - 4)" => 2.0, ],
			["comments.id" => 4, "comments.text" => "awd2018", "comments.rok" => 2018, "comments.userId" => 20, "1 + 2 + (3 - 4)" => 2.0, ],
			["comments.id" => 5, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 500, "1 + 2 + (3 - 4)" => 2.0, ],
			["comments.id" => 6, "comments.text" => "wdw2015", "comments.rok" => 2015, "comments.userId" => 1, "1 + 2 + (3 - 4)" => 2.0, ],
			["comments.id" => 8, "comments.text" => "awd2015", "comments.rok" => 2015, "comments.userId" => 1, "1 + 2 + (3 - 4)" => 2.0, ],
			["comments.id" => 9, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 1, "1 + 2 + (3 - 4)" => 2.0, ],
			["comments.id" => 10, "comments.text" => "awdawddd2020", "comments.rok" => 2020, "comments.userId" => 1, "1 + 2 + (3 - 4)" => 2.0, ],
			["comments.id" => 11, "comments.text" => "awdawdqweawd2015", "comments.rok" => 2015, "comments.userId" => 1, "1 + 2 + (3 - 4)" => 2.0, ],
		];
	}
}
