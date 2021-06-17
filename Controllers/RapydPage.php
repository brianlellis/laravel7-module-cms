<?php

namespace Rapyd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Rapyd\Model\CmsPage;

class RapydPage extends Controller
{
  public static function get_pages($order_by = 'id')
  {
    $data = CmsPage::orderBy($order_by)->paginate(25);
    return $data;
  }

  public function store_content(Request $request)
  {
    $validator = $this->validate($request, ['content_title' => 'required']);
    $new_page  = $request->content_id ? $new_page = CmsPage::find($request->content_id) : new CmsPage;
    $success_message = 'CMS content successfully updated';
    // NOTE: LEAVING HERE AS WARNING!!!!
    // YOU CANNOT CLEAN THE CONTENT AS IT BREAKS THE WYSIWIG DUE TO COMMENTS NEEDED
    //$clean_content = preg_replace('/<!--(.*)-->|\\r\\n/Uis', '', $validator['cms_page_editor']);
    
    $new_page->title        = $validator['content_title'];
    $new_page->meta_desc    = $request->content_meta_description ?? '';
    $new_page->url_slug     = $request->url_slug ?? \RapydCore::slugify($validator['content_title']);
    $new_page->is_published             = $request->is_published ?? 0;
    $new_page->content_body             = $request->cms_content_editor ?? '';
    $new_page->page_script              = $request->content_script ?? '';
    $new_page->page_css                 = $request->content_css ?? '';
    $new_page->content_wrapper_path     = $request->content_wrapper_path ?? '';
    $new_page->header_response_override = $request->header_response_override ?? '';
    $new_page->social_page_title        = $request->social_page_title ?? '';
    $new_page->social_page_img          = $request->social_page_img ?? '';
    $new_page->social_page_desc         = $request->social_page_desc ?? '';
    $new_page->save();

    $new_page->categories()->detach();
    if ($request->category && $request->category[0] !== null) {
      $new_page->categories()->attach($request->category);
    }

    if ($request->content_id) {
      \FullText::reindex_record('\\Rapyd\\Model\\CmsPage', $request->content_id);
      return back()->with('success', $success_message);
    }
    return redirect(request()->getSchemeAndHttpHost().'/admin/cms/page/dashboard')->with('success', $success_message);
  }

  public function delete($content_id)
  {
    CmsPage::find($content_id)->delete();
    return back()->with('success', 'Page successfully removed');
  }

  public static function laraberg_custom_blocks()
  {
    $dom                  = '';
    $base_category_files  = array_map(function ($dir) {
      return basename($dir);
    }, glob(base_path() . '/resources/Public/js/laraberg/blocks/*.{js}', GLOB_BRACE));

    foreach ($base_category_files as $category_file) {
      $randomize_block_name = substr(str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyz', ceil(10/strlen($x)) )),1,10);

      $dom .= '<script type="text/babel">';
      $dom .= "const {$randomize_block_name} =  ";
      $dom .= \File::get(base_path().'/resources/Public/js/laraberg/blocks/' . $category_file);
      $dom .= "Laraberg.registerBlock('{$randomize_block_name}/{$randomize_block_name}', {$randomize_block_name})";
      $dom .= '</script>';
    }
    return $dom;
  }

  public static function laraberg_custom_cats()
  {
    $dom                  = '';
    $base_category_files  = array_map(function ($dir) {
      return basename($dir);
    }, glob(base_path() . '/resources/Public/js/laraberg/categories/*.{js}', GLOB_BRACE));

    foreach ($base_category_files as $category_file) {
      $dom .= '<script>' . \File::get(base_path().'/resources/Public/js/laraberg/categories/' . $category_file) . '</script>';
    }
    return $dom;
  }
}
