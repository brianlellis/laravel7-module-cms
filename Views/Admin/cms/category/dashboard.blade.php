@php
    use Rapyd\Model\CmsCategory;
    $data = CmsCategory::orderBy('type')->orderBy('name')->paginate(25);

    if(request('search')) {
      $data = CmsCategory::where('name', 'like', '%'.request('search').'%')
                  ->orWhere('type', 'like', '%'.request('search').'%')
                  ->orWhere('slug', 'like', '%'.request('search').'%')
                  ->paginate(25)
                  ->appends(['search' => request('search')]);
    }
@endphp

@can('cms-category-view')
  @dashboard_table_header('
      Categorie,
      /admin/cms/category/create,
      cms_categories
  ')

  @dashboard_table('Type, Name, Description,  Slug, Action,'{!! $data->render() !!}, 'hide_sort')
    @foreach ($data as $key => $category)
      <tr>
        <td>{{ $category->type }}</td>
        <td>{{ $category->name }}</td>
        <td>{{ $category->description }}</td>
        <td>{{ $category->slug }}</td>
        <td>
          <a class="btn btn-primary btn-sm font-weight-bold" href="/admin/cms/category/create?category_id={{$category->id}}">Edit</a>
          <form action="/api/cms/category/{{$category->id}}/delete" method="POST"
            style="display: inline;"
            onSubmit="return confirm('Are you sure you want to delete this category?')">
            @csrf
            <button class="btn btn-danger btn-sm font-weight-bold" type="submit" >Delete</button>
          </form>
        </td>
      </tr>
    @endforeach
  @end_dashboard_table
@endcan
