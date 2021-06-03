@php
    use Rapyd\Model\CmsBlogPost;
    use Rapyd\Model\CmsCategory;
    use Rapyd\Model\CmsContentWrapper;

    $post = false;
    $user = Auth::user();
    $posted_at = \Carbon\Carbon::now()->format('Y-m-d');
    $categories = CmsCategory::where('type', 'blog')->get();
    $cms_content_wrappers = CmsContentWrapper::get();

    if(request('blog_id')) {
        $post = CmsBlogPost::find(request('blog_id'));
        $posted_at = \Carbon\Carbon::parse($post->posted_at)->format('Y-m-d');
    }

    $authors = App\User::permission('sys-menu-page-blog-hub-add-post')->get();
@endphp

{{-- EITHER YOU HAVE PERM TO EDIT ANY POST OR YOU CAN ONLY EDIT YOUR OWN --}}
@if(
  (request('blog_id') && $user->can('cms-blog-create')) ||
  (isset($post->user_id) && $user->id === $post->user_id) ||
  !request('blog_id')
)
  <form method='post' action={{ $post ? "/api/cms/blog/$post->id/update" : '/api/cms/blog/store'}} enctype="multipart/form-data">
      @csrf
      <div class="row">
          {{-- POST BODY --}}
          <div class="col-md-9 col-sm-12">
            <div class="card">
              <div class="card-header">
                <h2 class="card-title">
                  @if ($post)
                      Edit Post
                  @else
                      Add Post
                  @endif
                </h2>
              </div>
              <div id="post_body" class="card-body">
                  {{-- POST TITLE --}}
                  <div class="form-group">
                      <label for="title">Blog Post Title</label>
                      <input type="text" class="form-control" id="title" name='title' value="{{$post->title ?? old('title')}}" required>
                  </div>

                  <div class="row">
                      <div class="col-md-6">
                          {{-- SHORT DESCRIPTION --}}
                          <div class="form-group">
                              <label for="short_description">Short Desc (optional)</label>
                              <textarea class="form-control" id="short_description" name='short_desc'>{{$post->short_desc ?? old('short_desc')}}</textarea>
                          </div>
                      </div>

                      {{-- IF YOU DONT HAVE PERMISSION TO CREATE POST YOU CANT SET THE CMS SETTINGS --}}
                      @if($user->can('cms-blog-create'))
                        <div class="col-md-6">
                          {{-- Meta DESCRIPTION --}}
                          <div class="form-group">
                              <label for="meta_description">Meta Desc (optional)</label>
                              <textarea class="form-control" id="meta_description" name='meta_desc'>{{$post->meta_desc ?? old('meta_desc')}}</textarea>
                          </div>
                        </div>
                      @endif
                  </div>

                  {{-- POST BODY --}}
                  <div class="form-group">
                      <textarea class="form-control richTextBox" name="content_body">{{$post->content_body ?? old('content_body')}}</textarea>
                  </div>

                {{-- IF YOU DONT HAVE PERMISSION TO CREATE POST YOU CANT SET THE CMS SETTINGS --}}
                @if($user->can('cms-blog-create'))
                  <div class="row">
                      <div class="col-md-6">
                          {{-- Page Script --}}
                          <div class="form-group">
                              <label for="page_css">Page CSS</label>
                              <textarea class="form-control tab-able" rows="10" placeholder="Post specific CSS..." name="page_css" id="page_css">{{$post->page_css ?? old('page_css')}}</textarea>
                          </div>
                      </div>
                      <div class="col-md-6">
                          {{-- Page Script --}}
                          <div class="form-group">
                              <label for="page_script">Page Script</label>
                              <textarea class="form-control tab-able" name="page_script" rows="10" placeholder="Post specific JS...">{{$post->page_script ?? old('page_script')}}</textarea>
                          </div>
                      </div>
                  </div>
                @endif
              </div>
            </div>
          </div>

          {{-- RIGHT SIDEBAR --}}
          <div class="col-md-3 col-sm-12" id="post_right_sidebar">
              {{-- IF YOU DONT HAVE PERMISSION TO CREATE POST YOU CANT SET THE CMS SETTINGS --}}
              <div class="card">
                <div class="card-body">

                  @if($user->can('cms-blog-create'))
                    {{-- POSTED ON DATE --}}
                    <div class="form-group">
                        <label for="posted_at">Written on</label>
                        <input type="date" class="form-control" id="posted_at" name='posted_at' value="{{$posted_at}}" required>
                    </div>

                    {{-- CONTENT WRAPPER --}}
                    @if($cms_content_wrappers->first())
                      <div class="form-group">
                        <label>Content Template</label>
                        <select class="form-control" id="content_wrapper_path" name='content_wrapper_path'>
                          <option value="">None</option>
                          @foreach ($cms_content_wrappers as $wrapper)
                            <option value="{{$wrapper->blade_path}}" @if(isset($post->id) && ($wrapper->blade_path === $post->content_wrapper_path)) selected @endif>
                              {{$wrapper->description}}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    @endif

                    {{-- FEATURED? --}}
                    <div class="form-group">
                        <label for="is_featured">Featured?</label>
                        <select name='is_featured' class='form-control' id='is_featured'>
                            <option value='1' @if($post && $post->is_featured) selected @endif>
                                Featured
                            </option>
                            <option value='0' @if($post && !$post->is_featured) selected @endif>
                                Not Featured
                            </option>
                        </select>
                    </div>

                    {{-- PUBLISHED? --}}
                    <div class="form-group">
                        <label for="is_published">Published?</label>
                        <select name='is_published' class='form-control' id='is_published'>
                            <option value='1' @if($post && $post->is_published) selected @endif>
                                Published
                            </option>
                            <option value='0' @if($post && !$post->is_published) selected @endif>
                                Not Published
                            </option>
                        </select>
                    </div>

                    {{-- PRESS RELEASE? --}}
                    <div class="form-group">
                        <label for="is_press_release">Press Release?</label>
                        <select name='is_press_release' class='form-control' id='is_press_release' aria-describedby='is_press_release_help'>
                            <option value='1' @if($post && $post->is_press_release) selected @endif>
                                Yes
                            </option>
                            <option value='0' @if($post && !$post->is_press_release) selected @endif>
                                No
                            </option>
                        </select>
                    </div>

                    {{-- POST SLUG --}}
                    <div class="form-group">
                        <label for="url_slug">Blog Post Slug</label>
                        <input type="text" class="form-control" id="url_slug" name='url_slug' value="{{$post->url_slug ?? old('url_slug')}}">
                        <small>Leave blank to generate slug</small>
                    </div>

                    {{-- POST IMAGE --}}
                    <div class="form-group">
                        <label>Featured Image</label>
                        @if($post && $post->image_featured)
                        <div>
                            <img src="{{asset($post->image_featured)}}" class="img-fluid" style="max-height: 200px;">
                        </div>
                        @endif
                        <div class="form-group">
                            <input class="form-control" type="file" name="image_featured" id="image_featured" accept=".jpg,.jpeg,.png">
                        </div>
                    </div>

                    {{-- CATEGORIES --}}
                    <div class='form-group'>
                        <label>Categories:</label>
                        <select name="category[]" id="category" class="form-control" multiple>
                            <option value="" @if($post && $post->categories()->count() == 0)
                                selected
                                @endif>None</option>
                            @foreach ($categories as $category)
                              <option value="{{$category->id}}" @if ($post && $post->categories->contains($category->id))
                                  selected
                                  @endif
                                  >{{$category->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Authors --}}
                    <div class="form-group">
                        <label for="user_id">Author</label>
                        <select name="user_id" class="form-control">
                            {{-- Current Auth User --}}
                            <option value="{{auth()->user()->id}}">{{auth()->user()->email}}</option>
                            {{-- All Authors --}}
                            @can('cms-blog-select-author')
                              @foreach ($authors as $author)
                                  @if($author->id !== auth()->user()->id)
                                  <option value="{{$author->id}}"
                                      @if($post && $post->user_id === $author->id) checked @endif>{{$author->email}}</option>
                                  @endif
                              @endforeach
                            @endcan
                        </select>
                    </div>
                  @else
                    <input type="hidden" name="posted_at" value="{{$posted_at}}">
                    <input type="hidden" name="is_published" value="0">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="hidden" name="is_press_release" value="0">
                    <input type="hidden" name="user_id" value="{{$user->id}}">
                  @endif

                  <input id="save_post" type='submit' class='btn btn-primary' value='Save Changes'>
                </div>
              </div>
          </div>
      </div>

      @section('page_bottom_scripts')
          <script>
              const cssTextarea = document.querySelector('#page_css')

              function tinymce_init_callback() {
                  const iframe = document.querySelector('iframe')
                  const doc = iframe.contentWindow.document;
                  const head = doc.querySelector('head')

                  let style;
                  style = document.createElement('style')
                  style.innerHTML = cssTextarea.value;
                  head.appendChild(style)


                  cssTextarea.addEventListener('keyup', (e) => {
                      head.removeChild(style)
                      style.innerHTML = cssTextarea.value
                      head.appendChild(style)
                  })
              }

              document.querySelectorAll('.tab-able').forEach(field => {
                  field.addEventListener('keydown', e => {
                      if ( e.key === 'Tab' && !e.shiftKey ) {
                          document.execCommand('insertText', false, "\t");
                          e.preventDefault();
                          return false;
                      }
                  })
              });
          </script>

          <script src="https://cdn.tiny.cloud/1/sciq4hgol5faritl7pux7z8gazeni4fj5bkebpkgbkueunhk/tinymce/5/tinymce.min.js"></script>
          <script>
              tinymce.remove('textarea.richTextBox');
              tinymce.init({
                  selector: 'textarea.richTextBox'
                  , plugins: 'print preview paste importcss searchreplace autolink directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons'
                  , menubar: false
                  , toolbar: 'undo redo | bold italic underline strikethrough | fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent | table | numlist bullist | forecolor backcolor removeformat | charmap emoticons | link anchor | ltr rtl'
                  , toolbar_sticky: true
                  , image_advtab: true
                  , content_css: '//www.tiny.cloud/css/codepen.min.css'
                  , importcss_append: true
                  , height: 400
                  , file_browser_callback: function(field_name, url, type, win) {
                      $('#upload_file').trigger('click');
                  }
                  , height: 600
                  , image_caption: true
                  , quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable'
                  , noneditable_noneditable_class: "mceNonEditable"
                  , toolbar_mode: 'sliding'
                  , init_instance_callback: function(editor) {
                      if (typeof tinymce_init_callback !== "undefined") {
                          tinymce_init_callback(editor);
                      }
                  }
                  , setup: function(editor) {
                      if (typeof tinymce_setup_callback !== "undefined") {
                          tinymce_setup_callback(editor);
                      }
                  }
              });

          </script>
      @endsection
  </form>
@endif
