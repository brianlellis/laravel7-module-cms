<?php

namespace Rapyd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Rapyd\Model\CmsBlogPost;
use Rapyd\Model\CmsCategory;
use Rapyd\Model\CmsContentWrapper;

class RapydBlog extends Controller
{
  public static function get_posts($order_by = 'updated_at', $direction = 'DESC')
  {
    return CmsBlogPost::orderBy($order_by, $direction)->paginate(25);
  }

  public static function get_post()
  {
    $url_slug   = request()->path();
    $url_prefix = \SettingsSite::get('cms_blog_post_prefix');
    $url_slug   = ltrim(str_replace("$url_prefix", '', $url_slug), '/');

    return \Cache::rememberForever(env('APP_DOMAIN')."cmspost_{$url_slug}", 
          function() use($url_slug) {
            return CmsBlogPost::where('url_slug', $url_slug)->first();
          });
  }

  public function store()
  {
    $blog = CmsBlogPost::create($this->make_blog());
    $blog->categories()->attach(request()->category);

    \RapydEvents::send_mail('cmsblog_created', ['passed_cms_post'=>$blog]);

    if ($blog->url_slug) {
      \Cache::rememberForever(env('APP_DOMAIN')."cmspost_{$blog->url_slug}", 
          function() use($blog) {
            return $blog;
          });
    }
    return redirect(request()->getSchemeAndHttpHost().'/admin/cms/blog/dashboard')->with('success', 'Post Created');
  }

  public function update(CmsBlogPost $blog)
  {
    \Cache::forget(env('APP_DOMAIN')."cmspost_{$blog->url_slug}");
    
    $blog->update($this->make_blog());
    $blog->categories()->detach();
    if (request()->category && request()->category[0] !== null) {
      $blog->categories()->attach(request()->category);
    }

    if ($blog->url_slug) {
      \Cache::rememberForever(env('APP_DOMAIN')."cmspost_{$blog->url_slug}", 
          function() use($blog) {
            return $blog;
          });
    }
    \RapydEvents::send_mail('cmsblog_updated', ['passed_cms_post'=>$blog]);
    \FullText::reindex_record('\\Rapyd\\Model\\CmsBlogPost', $blog->id);
    return back()->with('success', 'Post Updated');
  }

  public function delete(CmsBlogPost $blog)
  {
    \Cache::forget(env('APP_DOMAIN')."cmspost_{$blog->url_slug}");
    \RapydEvents::send_mail('cmsblog_removed', ['passed_cms_post'=>$blog]);
    $blog->delete();
    return back()->with('success', 'Post Deleted');
  }


  public function make_blog()
  {
    $data = request()->validate([
      'user_id'               => 'required',
      'title'                 => 'required',
      'content_body'          => 'required',
      'posted_at'             => 'required',
      'is_featured'           => 'required',
      'is_press_release'      => 'required',
      'is_published'          => 'required',
      'subtitle'              => 'nullable',
      'short_desc'            => 'nullable',
      'meta_desc'             => 'nullable',
      'page_script'           => 'nullable',
      'page_css'              => 'nullable',
      'image_featured'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      'url_slug'              => 'nullable', //if null auto generate,
      'content_wrapper_path'  => 'nullable',
      'social_page_title'      => 'nullable',
      'social_page_img'       => 'nullable',
      'social_page_desc'      => 'nullable'
    ]);

    if (!isset($data['url_slug'])) {
      $data['url_slug'] = \RapydCore::slugify($data['title']);
    }

    if (isset($data['image_featured'])) {
      $image = request()->file('image_featured');
      $image->move(public_path('blog/images'), $image->getClientOriginalName());
      // GREP FIX FOR AMAZON SOLUTION
      $data['image_featured'] = '/blog/images/' . $image->getClientOriginalName();
    }
    return $data;
  }

  public static function getAllCategories()
  {
    return CmsCategory::orderBy("name")->get();
  }
}
