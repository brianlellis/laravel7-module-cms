@php
  $data = \ui_AdminDashboard::dashboard_method('cms_page');
@endphp

@can('cms-page-view')
  @dashboard_table_header('
  Page,
  /admin/cms/page/create,
  cms_pages
  ')

  @dashboard_table(' Title,Published?, Url, Action,'{!! $data->paginate !!})
  @foreach ($data->pages as $key => $page)
    <tr>
      <td>
        <a href='{{ url($page->url_slug) }}' target="blank">{{ $page->title }}</a>
      </td>
      <td>
        @if ($page->is_published)
          Yes
        @else
          No
        @endif
      </td>
      <td>
        {{ $page->url_slug }}
      </td>
      <td>
        @can('cms-page-update')
          <a class="btn btn-sm font-weight-bold btn-primary" href="/admin/cms/page/create?content_id={{ $page->id }}">Edit</a>
        @endcan

        @can('cms-page-delete')
          <form action="/api/cms/page/delete/{{ $page->id }}" method="GET" class="d-inline"
            onsubmit="return confirm('Are you sure you want to delete this page?')">
            <button class="btn btn-sm font-weight-bold btn-danger" type="submit">Remove</button>
          </form>
        @endcan
      </td>
    </tr>
  @endforeach
  @end_dashboard_table({!! $data->paginate !!})
@endcan
