<?php

namespace Rapyd;

use App\Http\Controllers\Controller;
use Rapyd\Model\CmsContentWrapper;
use Illuminate\Support\ServiceProvider;

class RapydWrapper extends Controller
{
  public function store()
  {
    CmsContentWrapper::create($this->make_wrapper());
    return redirect('/admin/cms/wrapper/dashboard');
  }

  public function update(CmsContentWrapper $wrapper)
  {
    $wrapper->update($this->make_wrapper());
    return redirect('/admin/cms/wrapper/dashboard');
  }

  public function delete(CmsContentWrapper $wrapper)
  {
    $wrapper->delete();
    return back();
  }

  public function get($wrapper)
  {
    if (file_exists(resource_path('Public/views/content-wrapper/' . $wrapper . '.blade.php'))) {
      $view = file_get_contents(resource_path('Public/views/content-wrapper/' . $wrapper . '.blade.php'));
      preg_match('/<style[^>]*>([^<]+)<\/style>/i', $view, $styles);
      return ['success' => true, 'styles' => $styles[1]];
    }
    return ['success' => false];
  }

  public function make_wrapper()
  {
    $data = request()->validate([
      'description'    => 'required',
      'blade_path'     => 'required'
    ]);
    return $data;
  }
}
