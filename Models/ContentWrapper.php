<?php

namespace Rapyd\Model;

use Illuminate\Database\Eloquent\Model;

class CmsContentWrapper extends Model
{
  protected $connection = 'mysql';
  protected $table      = 'cms_content_wrappers';
  protected $colKey     = 'id';
  protected $guarded    = [];
  public $timestamps    = false;
}
