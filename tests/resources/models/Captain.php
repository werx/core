<?php

namespace werx\Core\Tests\App\Models;

use werx\Core\Model;

class Captain extends Model
{
	public $timestamps = false;

	public static function search($params = [])
	{
		$query = self::queryBuilder();
		
		if (array_key_exists('first_name', $params)) {
			$query->where('first_name', 'like', $params['first_name'] . '%');
		}

		if (array_key_exists('last_name', $params)) {
			$query->where('last_name', 'like', $params['last_name'] . '%');
		}
		
		return $query->get();
	}
}
