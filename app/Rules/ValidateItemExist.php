<?php

namespace App\Rules;


use App\Helpers\Status;
use App\ItemStock;
use Illuminate\Contracts\Validation\Rule;

class ValidateItemExist implements Rule {


	/**
	 * @var ItemStock
	 */
	private $itemStock;

	public function __construct() {


		$this->itemStock = new ItemStock();
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value) {


		$valid = true;
		$response = $this->itemStock->withRelatedModels()
			->whereId($value)
			->whereHas('sizeOption', function ($query) {
				$query->whereStatusOptionId(Status::ACTIVE);

			})
			->whereHas('itemVariant', function ($query) {
				$query->whereHas('colorOption', function ($query) {
					$query->whereStatusOptionId(Status::ACTIVE);
				});
			})->first();

		if (!$response) {
			$valid = false;
		}

		return $valid;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {

		return 'Item does not exist.';

	}
}
