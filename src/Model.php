<?php

namespace werx\Core;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
	public static function queryBuilder()
	{
		$self = get_called_class();
		return DB::table((new $self)->getTable());
	}
}
