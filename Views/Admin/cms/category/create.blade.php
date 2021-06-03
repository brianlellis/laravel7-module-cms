@php
    use Rapyd\Model\CmsCategory;

    $category = false;

    if(request('category_id')) {
        $category = CmsCategory::find(request('category_id'));
    }
@endphp

@can('cms-category-create')
  <h1>Add Category</h1>

  <form action={{ $category ? "/api/cms/category/$category->id/update" : "/api/cms/category/store"}} method="POST">
      @csrf
      <div class="row">
          {{-- Left Side --}}
          <div class="col-md-9 col-sm-12">
              <div id="post_body">
                  {{-- Category Name --}}
                  <div class="form-group">
                      <label for="name">Category Name</label>
                      <input type="text" class="form-control" id="name" name='name'
                          value="{{$category->name ?? old('name')}}" required>
                  </div>

                  {{-- Category Description --}}
                  <div class="form-group">
                      <label for="description">Description ( Optional )</label>
                      <textarea class="form-control" id="description"
                          name="description" rows="10">{{$category->description ?? old('description')}}</textarea>
                  </div>
              </div>
          </div>

          {{-- RIGHT SIDEBAR --}}
          <div class="col-md-3 col-sm-12" id="post_right_sidebar">
              {{-- FEATURED? --}}
              <div class="form-group">
                  <label for="type">Type?</label>
                  <select class='form-control' id='type' name='type'>
                      <option value="blog" @if($category && $category->type === 'blog') selected @endif>Blog</option>
                      <option value="page" @if($category && $category->type === 'page') selected @endif>Page</option>
                  </select>
              </div>

              <div class="form-group">
                  <label for="slug">Slug</label>
                  <input type="text" class="form-control" name="slug" value="{{$category->slug ?? old('slug')}}">
                  <small>Leave blank to generate a slug</small>
              </div>

              <input id="save_post" type='submit' class='btn btn-primary' value='Save Changes'>
          </div>
      </div>
  </form>
@endcan
