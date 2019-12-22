<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2019. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Factory;

use Illuminate\Support\Carbon;
//use Faker\Generator as Faker;

class InvoiceItemFactory
{
	public static function create() :\stdClass
	{
		$item = new \stdClass;
		$item->quantity = 0;
		$item->cost = 0;
		$item->product_key = '';
		$item->notes = '';
		$item->discount = 0;
		$item->is_amount_discount = true;
		$item->tax_name1 = '';
		$item->tax_rate1 = 0;
		$item->tax_name2 = '';
		$item->tax_rate2 = 0;
		$item->tax_name3 = '';
		$item->tax_rate3 = 0;
		$item->sort_id = 0;
		$item->line_total = 0;
		$item->date = Carbon::now();
		$item->custom_value1 = NULL;
		$item->custom_value2 = NULL;
		$item->custom_value3 = NULL;
		$item->custom_value4 = NULL;

		return $item;

	}

	/**
	 * Generates an array of dummy data for invoice items
	 * @param  int    $items Number of line items to create
	 * @return array        array of objects
	 */
	public static function generate(int $items = 1) :array
	{
		$faker = \Faker\Factory::create();

		$data = [];

		for($x=0; $x<$items; $x++)
		{

			$item = self::create();
			$item->quantity = $faker->numberBetween(1,10);
			$item->cost = $faker->randomFloat(2, 1, 1000);
			$item->line_total = $item->quantity * $item->cost;
			$item->is_amount_discount = true;
			$item->discount = $faker->numberBetween(1,10);
			$item->notes = $faker->realText(20);
			$item->product_key = $faker->word();
			$item->custom_value1 = $faker->realText(10);
			$item->custom_value2 = $faker->realText(10);
			$item->custom_value3 = $faker->realText(10);
			$item->custom_value4 = $faker->realText(10);
			$item->tax_name1 = 'GST';
			$item->tax_rate1 = '10.00';
			
			$data[] = $item;
		}
		
		return $data;
	}

}