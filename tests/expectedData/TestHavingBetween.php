<?php

namespace PQL\Tests\InputData;

class TestHavingBetween implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 3, "comments.text" => "wad2015", "comments.rok" => 2015, "comments.userId" => 3, "COUNT(comments.rok)" => 4, "SUM(comments.rok)" => 8060, ],
			["comments.id" => 6, "comments.text" => "wdw2015", "comments.rok" => 2015, "comments.userId" => 1, "COUNT(comments.rok)" => 4, "SUM(comments.rok)" => 8060, ],
			["comments.id" => 8, "comments.text" => "awd2015", "comments.rok" => 2015, "comments.userId" => 1, "COUNT(comments.rok)" => 4, "SUM(comments.rok)" => 8060, ],
			["comments.id" => 11, "comments.text" => "awdawdqweawd2015", "comments.rok" => 2015, "comments.userId" => 1, "COUNT(comments.rok)" => 4, "SUM(comments.rok)" => 8060, ],
		];
	}
}
