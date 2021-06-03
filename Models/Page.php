<?php

namespace Rapyd\Model;

use Illuminate\Database\Eloquent\Model;
use Rapyd\Model\CmsContentWrapper;
use Rapyd\Model\CmsCategory;

class CmsPage extends Model
{
  use \Swis\Laravel\Fulltext\Indexable;

  protected $indexContentColumns = [];

  protected $indexTitleColumns = [
    'title',
    'content_body',
    'categories.name'
  ];

  protected $table   = 'cms_pages';
  protected $colKey  = 'id';
  protected $guarded = [];

  public function content_wrapper()
  {
    return $this->hasOne(CmsContentWrapper::class, 'id', 'content_wrapper_id');
  }

  public function categories()
  {
    return $this->belongsToMany(CmsCategory::class, 'cms_pages_categories', 'page_id', 'category_id');
  }
}
