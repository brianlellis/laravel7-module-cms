@php
  use Rapyd\Model\CmsContentWrapper;
  $data = CmsContentWrapper::orderBy('description')->paginate(25);

  if(request('search')) {
      $data = CmsContentWrapper::where('description', 'like', '%'.request('search').'%')
                  ->orWhere('blade_path', 'like', '%'.request('search').'%')
                  ->paginate(25)
                  ->appends(['search' => request('search')]);
    }

@endphp

@can('cms-category-view')
  @dashboard_table_header('
  Wrapper,
  /admin/cms/wrapper/create,
  cms_categories
  ')

  @dashboard_table('ID #, Description, Blade Path, Actions,'{!! $data->render() !!}, 'hide_sort')
  @foreach ($data as $key => $wrapper)
    <tr>
      <td>{{ $wrapper->id }}</td>
      <td>{{ $wrapper->description }}</td>
      <td>{{ $wrapper->blade_path }}</td>
      <td>
        <a class="btn btn-primary btn-sm font-weight-bold" href="/admin/cms/wrapper/create?wrapper_id={{ $wrapper->id }}">Edit</a>
        <form action="/api/cms/wrapper/{{ $wrapper->id }}/delete" method="POST" style="display: inline;"
          onSubmit="return confirm('Are you sure you want to delete this wrapper?')">
          @csrf
          <button class="btn btn-danger btn-sm font-weight-bold" type="submit">Delete</button>
        </form>
      </td>
    </tr>
  @endforeach
  @end_dashboard_table
@endcan
