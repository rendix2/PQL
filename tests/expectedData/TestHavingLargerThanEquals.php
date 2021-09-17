<?php

namespace PQL\Tests\InputData;

class TestHavingLargerThanEquals implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 3, "comments.text" => "wad2015", "comments.rok" => 2015, "comments.userId" => 3, "COUNT(comments.rok)" => 4, "SUM(comments.rok)" => 8060, ],
			["comments.id" => 5, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 500, "COUNT(comments.rok)" => 3, "SUM(comments.rok)" => 6060, ],
			["comments.id" => 6, "comments.text" => "wdw2015", "comments.rok" => 2015, "comments.userId" => 1, "COUNT(comments.rok)" => 4, "SUM(comments.rok)" => 8060, ],
			["comments.id" => 8, "comments.text" => "awd2015", "comments.rok" => 2015, "comments.userId" => 1, "COUNT(comments.rok)" => 4, "SUM(comments.rok)" => 8060, ],
			["comments.id" => 9, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 1, "COUNT(comments.rok)" => 3, "SUM(comments.rok)" => 6060, ],
			["comments.id" => 10, "comments.text" => "awdawddd2020", "comments.rok" => 2020, "comments.userId" => 1, "COUNT(comments.rok)" => 3, "SUM(comments.rok)" => 6060, ],
			["comments.id" => 11, "comments.text" => "awdawdqweawd2015", "comments.rok" => 2015, "comments.userId" => 1, "COUNT(comments.rok)" => 4, "SUM(comments.rok)" => 8060, ],
		];
	}
}
