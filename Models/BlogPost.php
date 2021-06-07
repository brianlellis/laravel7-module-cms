<?php

namespace Rapyd\Model;

use Illuminate\Database\Eloquent\Model;
use Rapyd\Model\CmsCategory;
use App\User;

class CmsBlogPost extends Model
{
  use \Swis\Laravel\Fulltext\Indexable;

  protected $connection = 'mysql';
  protected $indexContentColumns = [];

  protected $indexTitleColumns = [
    'title',
    'content_body',
    'categories.name'
  ];

  protected $table   = 'cms_blog_posts';
  protected $colKey  = 'id';
  protected $guarded = [];

  public $dates = [
    'posted_at'
  ];

  public $casts = [
    'is_featured' => 'boolean',
    'is_published' => 'boolean',
    'is_press_release' => 'boolean',
  ];

  public function categories()
  {
    return $this->belongsToMany(CmsCategory::class, 'cms_blog_categories', 'blog_id', 'category_id');
  }

  public function author()
  {
    return $this->hasOne(User::class, 'id', 'user_id');
  }

  public static function front_page_posts($limit = 4)
  {
    return self::orderBy('posted_at', 'DESC')
                ->where('is_featured', 1)
                ->where('is_published', 1)
                ->limit($limit)
                ->get();
  }

  public function content_summary($length = 350)
  {
    if ($this->content_short_desc) {
      return strip_tags($this->content_short_desc);
    } else {
      return strip_tags(substr($this->content_body, 0, $length).'...');
    }
  }
}
