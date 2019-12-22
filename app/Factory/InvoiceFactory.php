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

use App\DataMapper\ClientSettings;
use App\DataMapper\CompanySettings;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class InvoiceFactory
{
	public static function create(int $company_id, int $user_id) :Invoice
	{
		$invoice = new Invoice();
		$invoice->status_id = Invoice::STATUS_DRAFT;
		$invoice->number = null;
		$invoice->discount = 0;
		$invoice->is_amount_discount = true;
		$invoice->po_number = '';
		$invoice->footer = '';
		$invoice->terms = '';
		$invoice->public_notes = '';
		$invoice->private_notes = '';
		$invoice->date = null;
		$invoice->due_date = null;
		$invoice->partial_due_date = null;
		$invoice->is_deleted = false;
		$invoice->line_items = json_encode([]);
		$invoice->backup = json_encode([]);
		$invoice->tax_name1 = '';
		$invoice->tax_rate1 = 0;
		$invoice->tax_name2 = '';
		$invoice->tax_rate2 = 0;
		$invoice->custom_value1 = 0;
		$invoice->custom_value2 = 0;
		$invoice->custom_value3 = 0;
		$invoice->custom_value4 = 0;
		$invoice->amount = 0;
		$invoice->balance = 0;
		$invoice->partial = 0;
		$invoice->user_id = $user_id;
		$invoice->company_id = $company_id;
		$invoice->recurring_id = null;
		
		return $invoice;
	}
}