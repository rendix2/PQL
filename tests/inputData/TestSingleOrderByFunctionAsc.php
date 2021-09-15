<?php

namespace PQL\Tests\InputData;

class TestSingleOrderByFunctionAsc implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 8, "comments.text" => "awd2015", "comments.rok" => 2015, "comments.userId" => 1, "STRTOUPPER(comments.text)" => "AWD2015", ],
			["comments.id" => 4, "comments.text" => "awd2018", "comments.rok" => 2018, "comments.userId" => 20, "STRTOUPPER(comments.text)" => "AWD2018", ],
			["comments.id" => 5, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 500, "STRTOUPPER(comments.text)" => "AWDAWD2020", ],
			["comments.id" => 9, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 1, "STRTOUPPER(comments.text)" => "AWDAWD2020", ],
			["comments.id" => 10, "comments.text" => "awdawddd2020", "comments.rok" => 2020, "comments.userId" => 1, "STRTOUPPER(comments.text)" => "AWDAWDDD2020", ],
			["comments.id" => 11, "comments.text" => "awdawdqweawd2015", "comments.rok" => 2015, "comments.userId" => 1, "STRTOUPPER(comments.text)" => "AWDAWDQWEAWD2015", ],
			["comments.id" => 3, "comments.text" => "wad2015", "comments.rok" => 2015, "comments.userId" => 3, "STRTOUPPER(comments.text)" => "WAD2015", ],
			["comments.id" => 2, "comments.text" => "wda2021", "comments.rok" => 2021, "comments.userId" => 2, "STRTOUPPER(comments.text)" => "WDA2021", ],
			["comments.id" => 6, "comments.text" => "wdw2015", "comments.rok" => 2015, "comments.userId" => 1, "STRTOUPPER(comments.text)" => "WDW2015", ],
		];
	}
}
