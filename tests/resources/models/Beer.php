<?php

namespace werx\Core\Tests\App\Models;

use werx\Core\Model;

class Beer extends Model
{
	public $timestamps = false;
	public $connection = 'beers';
}