@php
  use Rapyd\Model\CmsContentWrapper;
  use Rapyd\Model\CmsPage;
  use Rapyd\Model\CmsCategory;
  $cms_content_wrappers = CmsContentWrapper::get();
  $content_id = Request::get('content_id');
  $categories = CmsCategory::where('type', 'page')->get();

  if($content_id) {
    $content_data = CmsPage::find($content_id);
  }

@endphp

@can('cms-page-create')
  {{-- DEPENDENCIES --}}
  <script src="https://unpkg.com/react@16.8.6/umd/react.production.min.js"></script>
  <script src="https://unpkg.com/react-dom@16.8.6/umd/react-dom.production.min.js"></script>
  {{-- NOTE: DO NOT TOUCH!!! REQUIRED FOR JSX USAGE --}}
  <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>

  {{-- LIBRARY --}}
  <link rel="stylesheet" href="{{ asset('admin_pub/vendor/laraberg/css/laraberg.css') }}">
  <script src="{{ asset('admin_pub/vendor/laraberg/js/laraberg.js') }}"></script>

  {{-- Bond Search --}}
  @include($fallback, ["blade_lookup" => "cms.components.bond-search"])

  {{-- HTML TO BIND TO --}}
  <form id="laraberg-form" action="{{ route('cms.page.store') }}" method="POST">
    @csrf
    <input type="hidden" name="content_id" value="{{ $content_id }}" />

    {{-- GENERAL CONTENT INFO --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Content General Info</h3>
        <div class="card-options">
          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-down"></i></a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8 col-sm-12">
            {{-- CONTENT TITLE --}}
            <div class="form-group">
              <label for="content_title">Page Title</label>
              <input type="text" class="form-control" required id="content_title" name='content_title' @if (isset($content_id)) value="{{ $content_data->title }}" @endif
              >
            </div>

            {{-- META DESCRIPTION --}}
            <div class="form-group">
              <label>Meta Desc (optional)</label>
              @if (isset($content_id) && $content_data->meta_desc)
                <textarea class="form-control" id="content_meta_description"
                  name='content_meta_description'>{{ $content_data->meta_desc }}</textarea>
              @else
                <textarea class="form-control" id="content_meta_description" name='content_meta_description'></textarea>
              @endif
            </div>

            {{-- URL SLUG --}}
            <div class="form-group">
              <label>URL Slug (Leave Blank to Auto Generate From Title)</label>
              <input class="form-control" id="url_slug" name='url_slug' @if (isset($content_id)) value="{{ $content_data->url_slug }}" @endif
              >
            </div>
          </div>

          <div class="col-md-4 col-sm-12">
            {{-- IS PUBLISHED --}}
            <div class="form-group">
              <label>Is Published?</label>
              <select class="form-control" id="is_published" name='is_published'>
                <option value=0 @if (isset($content_id) && !$content_data->is_published)
                  selected @endif>
                  No
                </option>
                <option value=1 @if (isset($content_id) && $content_data->is_published)
                  selected @endif>
                  Yes
                </option>
              </select>
            </div>

            {{-- CONTENT WRAPPER --}}
            @if ($cms_content_wrappers->first())
              <div class="form-group">
                <label>Content Template</label>
                <select class="form-control" id="content_wrapper_path" name='content_wrapper_path'>
                  <option value="">None</option>
                  @foreach ($cms_content_wrappers as $wrapper)
                    <option value="{{ $wrapper->blade_path }}" @if (isset($content_id) && $wrapper->blade_path === $content_data->content_wrapper_path)
                      selected
                  @endif>
                  {{ $wrapper->description }}
                  </option>
            @endforeach
            </select>
          </div>
          @endif

          {{-- HEADER RESPONSE OVERRIDE --}}
          {{--
          NOTE: See https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
          --}}
          <div class='form-group'>
            <label>Response Header Override:</label>
            <select class="form-control" id="header_response_override" name='header_response_override'>
              <option value="">None</option>
              @foreach ([301, 302, 404, 500] as $response_header)
                <option value="{{ $response_header }}" @if (isset($content_id) && $response_header === $content_data->header_response_override) selected
              @endif>
              {{ $response_header }}
              </option>
              @endforeach
            </select>
          </div>

          {{-- CATEGORIES --}}
          <div class='form-group'>
            <label>Categories:</label>
            <select name="category[]" id="category" class="form-control" multiple>
              <option value="" @if (isset($content_data) && $content_data->categories()->count() == 0)
                selected
                @endif>None</option>
              @foreach ($categories as $category)
                <option value="{{ $category->id }}" @if (isset($content_data) && $content_data->categories->contains($category->id))
                  selected
              @endif
              >{{ $category->name }}</option>
              @endforeach
            </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-group">
      @if (isset($content_id) && $content_data->content_body)
        <textarea id="cms_content_editor" name="cms_content_editor" hidden>{{ $content_data->content_body }}</textarea>
      @else
        <textarea id="cms_content_editor" name="cms_content_editor" hidden></textarea>
      @endif
    </div>

    <div class="card card-collapsed">
      <div class="card-header">
        <h3 class="card-title">Social Media Meta Tags</h3>
        <div class="card-options">
          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-down"></i></a>
        </div>
      </div>
      <div class="card-body row">
        <div class="col-12">
          <label for="page_css">Title</label>
          <input class="form-control" name="social_page_title">
        </div>
        <div class="col-12">
          <label for="page_css">Image Url</label>
          <input class="form-control" name="social_page_img">
        </div>
        <div class="col-12">
          <label for="page_css">Description</label>
          <input class="form-control" name="social_page_desc">
        </div>
      </div>
    </div>

    {{-- CSS & JS --}}
    <div class="card card-collapsed">
      <div class="card-header">
        <h3 class="card-title">Custom CSS & Javascript</h3>
        <div class="card-options">
          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-down"></i></a>
        </div>
      </div>
      <div class="card-body row">
        {{-- Page Script --}}
        <div class="form-group col-6">
          <label>Javascript</label>
          @if (isset($content_id) && $content_data->page_script)
            <textarea class="form-control card" name="content_script" rows="10"
              placeholder="Page specific JS...">{{ $content_data->page_script }}</textarea>
          @else
            <textarea class="form-control card" name="content_script" rows="10"
              placeholder="Page specific JS..."></textarea>
          @endif
        </div>

        {{-- Page Script --}}
        <div class="form-group col-6">
          <label>Page CSS</label>
          @if (isset($content_id) && $content_data->page_css)
            <textarea class="form-control card" rows="10" placeholder="Page specific CSS..." name="content_css"
              id="content_css_textarea">{{ $content_data->page_css }}</textarea>
          @else
            <textarea class="form-control card" rows="10" placeholder="Page specific CSS..." name="content_css"
              id="content_css_textarea"></textarea>
          @endif
        </div>
      </div>
    </div>

    <div class="form-group">
      <a href="@url('admin/dashboard')" class="btn btn-danger" type="submit">Cancel</a>
      <button class="btn btn-success" type="submit">Submit</button>
    </div>
  </form>

  {{-- INIT SCRIPT --}}
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
@endcan
