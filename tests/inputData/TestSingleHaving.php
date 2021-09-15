<?php

namespace PQL\Tests\InputData;

class TestSingleHaving implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 5, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 500, "COUNT(comments.rok)" => 3, "SUM(comments.rok)" => 6060, ],
			["comments.id" => 9, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 1, "COUNT(comments.rok)" => 3, "SUM(comments.rok)" => 6060, ],
			["comments.id" => 10, "comments.text" => "awdawddd2020", "comments.rok" => 2020, "comments.userId" => 1, "COUNT(comments.rok)" => 3, "SUM(comments.rok)" => 6060, ],
		];
	}
}
