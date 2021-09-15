<?php

namespace PQL\Tests\InputData;

class TestLimit implements ITestData
{
	public function getData(): array
	{
		return [
			["comments.id" => 4, "comments.text" => "awd2018", "comments.rok" => 2018, "comments.userId" => 20, "COUNT(comments.rok)" => 1, "SUM(comments.rok)" => 2018, ],
		];
	}
}
