<?php

class ExampleTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testBasicExample()
	{
		//$crawler = $this->client->request('GET', '/');

		//$this->assertTrue($this->client->getResponse()->isOk());

		$output = new Symfony\Component\Console\Output\ConsoleOutput();
		$output->writeln("now date : " . date("Y-m-d H:i:s"));

		$the_date = date("Y-m-d H:i:s", strtotime('+1 Hours 59 minutes'));
		$output->writeln("The date: " . $the_date );
 		$output->writeln("sleep for 5 sec...");
		sleep(1);
		$date2=  \Carbon\Carbon::createFromTimeStamp(strtotime($the_date))->diffForHumans(null, true);

		$output->writeln("The date2: " . $date2);
		$output->writeln("The min: " . \Carbon\Carbon::createFromTimeStamp(strtotime($the_date))->minute);

		if(\Carbon\Carbon::createFromTimeStamp(strtotime($the_date))->minute >= 1){

			$date3 = \Carbon\Carbon::createFromTimeStamp(strtotime($the_date))->addHour(1)->diffForHumans(null, true);
			$output->writeln("The date3: " . $date3);
		}




	}
}
