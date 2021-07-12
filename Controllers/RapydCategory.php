<?php

namespace Rapyd;

use App\Http\Controllers\Controller;
use Rapyd\Model\CmsCategory;

class RapydCategory extends Controller {
  public function store()
  {
    $cat = CmsCategory::create($this->make_category());
    \RapydEvents::send_mail('cmscat_created', ['cms_cat' => $cat]);
    return redirect(config('app.url').'/admin/cms/category/dashboard');
  }

  public function update(CmsCategory $category)
  {
    $category->update($this->make_category());
    \RapydEvents::send_mail('cmscat_updated', ['cms_cat' => $category]);
    return redirect(config('app.url').'/admin/cms/category/dashboard');
  }

  public function delete(CmsCategory $category)
  {
    \RapydEvents::send_mail('cmscat_removed', ['cms_cat' => $category]);
    $category->delete();
    return back();
  }

  public function make_category()
  {
    $data = request()->validate([
      'name'        => 'required',
      'type'        => 'required',
      'description' => 'nullable',
      'slug'        => 'nullable'
    ]);

    if(!isset($data['slug'])) {
      $data['slug'] = \RapydCore::slugify($data['name']);
    }
    return $data;
  }
}