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
  <form id="laraberg-form" method='post' action={{ $post ? "/api/cms/blog/$post->id/update" : '/api/cms/blog/store'}} enctype="multipart/form-data">
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
                      @if ($post->content_body ?? false)
                        <textarea id="cms_content_editor" name="content_body" hidden>{{ $post->content_body }}</textarea>
                      @else
                        <textarea id="cms_content_editor" name="content_body" hidden></textarea>
                      @endif
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

    {{-- INIT SCRIPT --}}
    {{-- DEPENDENCIES --}}
    <script src="https://unpkg.com/react@16.8.6/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@16.8.6/umd/react-dom.production.min.js"></script>
    {{-- NOTE: DO NOT TOUCH!!! REQUIRED FOR JSX USAGE --}}
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
    {{-- LIBRARY --}}
    <link rel="stylesheet" href="{{ asset('admin_pub/vendor/laraberg/css/laraberg.css') }}">
    <script src="{{ asset('admin_pub/vendor/laraberg/js/laraberg.js') }}"></script>
    <script>
      window.addEventListener('DOMContentLoaded', () => {
        Laraberg.init(
          'cms_content_editor', {
            height: '850px',
            laravelFilemanager: true,
            sidebar: false
          });
      });

      document.getElementById('laraberg-form').addEventListener('submit', () => {
        // PREVENT SHOWING UNSAVED CHANGES POPUP

        // get current edits log
        const postType = wp.data.select( 'core/editor' ).getCurrentPostType();
        const postId = wp.data.select( 'core/editor' ).getCurrentPostId();
        const currentEditsLog = wp.data.select( 'core' ).getEntityRecordNonTransientEdits('postType', postType, postId);

        // clean up all the edits by deleting all properties from the object
        for (let member in currentEditsLog) {
          if (currentEditsLog.hasOwnProperty(member)) {
            delete currentEditsLog[member];
          }
        }
      });

      const wrapperSelector = document.getElementById('content_wrapper_path')
      fetchWrapper(wrapperSelector.value)

      wrapperSelector.addEventListener('change', (e) => {
        fetchWrapper(e.target.value)
      })

      function fetchWrapper(value) {
        if(!value) return;

        fetch(`/api/cms/wrapper/${value}`)
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              updateStyles(data.styles)
            }
          })
      }

      function updateStyles(styles) {
        let reg = new RegExp(/([^\r\n,{}]+)(,(?=[^}]*{)|\s*{)/g)
        let selectors = [...styles.matchAll(reg)];
        selectors.forEach(selector => {
          if (selector[0].indexOf('.block-editor__typewriter') === -1) {
            styles = styles.replace(selector[0], '.block-editor__typewriter' + selector[0])
          }
        })
        let style = document.querySelector('style')
        style.innerText = style.innerText + styles
      }

    </script>

    {{-- CUSTOM CATEGORIES --}}
    {!! \RapydPage::laraberg_custom_cats() !!}

    {{-- CUSTOM BLOCKS --}}
    {!! \RapydPage::laraberg_custom_blocks() !!}
  </form>
@endif
