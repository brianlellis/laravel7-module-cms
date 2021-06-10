<?php

namespace Rapyd;

use App\Http\Controllers\Controller;
use Rapyd\Model\CmsCategory;

class RapydCategory extends Controller {
  public function store()
  {
    CmsCategory::create($this->make_category());
    return redirect(request()->getSchemeAndHttpHost().'/admin/cms/category/dashboard');
  }

  public function update(CmsCategory $category)
  {
    $category->update($this->make_category());
    return redirect(request()->getSchemeAndHttpHost().'/admin/cms/category/dashboard');
  }

  public function delete(CmsCategory $category)
  {
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