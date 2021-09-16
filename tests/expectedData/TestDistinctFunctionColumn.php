<?php

namespace PQL\Tests\InputData;

class TestDistinctFunctionColumn implements ITestData
{
	public function getData(): array
	{
		return [
			["STRTOUPPER(comments.text)" => "WDA2021", ],
			["STRTOUPPER(comments.text)" => "WAD2015", ],
			["STRTOUPPER(comments.text)" => "AWD2018", ],
			["STRTOUPPER(comments.text)" => "AWDAWD2020", ],
			["STRTOUPPER(comments.text)" => "WDW2015", ],
			["STRTOUPPER(comments.text)" => "AWD2015", ],
			["STRTOUPPER(comments.text)" => "AWDAWDDD2020", ],
			["STRTOUPPER(comments.text)" => "AWDAWDQWEAWD2015", ],
		];
	}
}
