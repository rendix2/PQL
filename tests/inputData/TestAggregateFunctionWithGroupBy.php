<?php

namespace PQL\Tests\InputData;

class TestAggregateFunctionWithGroupBy implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 2, "comments.text" => "wda2021", "comments.rok" => 2021, "comments.userId" => 2, "COUNT(comments.rok)" => 1, "SUM(comments.rok)" => 2021, ],
			["comments.id" => 4, "comments.text" => "awd2018", "comments.rok" => 2018, "comments.userId" => 20, "COUNT(comments.rok)" => 1, "SUM(comments.rok)" => 2018, ],
			["comments.id" => 8, "comments.text" => "awd2015", "comments.rok" => 2015, "comments.userId" => 1, "COUNT(comments.rok)" => 4, "SUM(comments.rok)" => 8060, ],
			["comments.id" => 9, "comments.text" => "awdawd2020", "comments.rok" => 2020, "comments.userId" => 1, "COUNT(comments.rok)" => 3, "SUM(comments.rok)" => 6060, ],
		];
	}
}
