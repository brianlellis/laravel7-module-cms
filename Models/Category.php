<?php

namespace Rapyd\Model;

use Illuminate\Database\Eloquent\Model;

class CmsCategory extends Model
{
  protected $connection = 'mysql';
  protected $table      = 'cms_categories';
  protected $guarded    = [];
  public $timestamps    = false;
}
