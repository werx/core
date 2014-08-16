<?php

namespace werx\Core\Tests\App\Models;

use werx\Core\Model;

class CaptainComplex extends Model
{
	public $timestamps = false;
	public $connection = 'example';
	public $table = 'captains';
}
