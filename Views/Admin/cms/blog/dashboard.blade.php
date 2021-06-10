@php
  $data = \ui_AdminDashboard::dashboard_method('cms_blog');
@endphp

{{-- EITHER YOU CAN VIEW ALL POST OR ONLY YOUR OWN --}}
@if($data->user->can('cms-blog-view'))
  @dashboard_table_header('
    Post,
    /admin/cms/blog/create,
    cms_blog_posts
  ')
@endif

@dashboard_table('ID #, Title, Author,Published?, Category, Action,'{!! $data->paginate !!})
  @foreach ($data->posts as $key => $post)
    <tr>
      <td>{{ $post->id }}</td>
      <td>{{ $post->title }}</td>
      <td>
        @can('user-update')
          <a href="@url('/admin/user/profile?user_id='){{ $post->user_id }}">{{$post->author->name_first}} {{$post->author->name_last}}</a>
        @else
          {{$post->author->name_first}} {{$post->author->name_last}}
        @endcan
      </td>
      <td>
        @if($post->is_published)
          Yes
        @else
          No
        @endif
      </td>
      <td>
        @foreach ($post->categories as $category)
            {{$category->name}},
        @endforeach
      </td>
      <td>
        <a class="btn btn-sm btn-primary font-weight-bold" target="_blank" href="@url('/'){{$post->url_slug}}">Preview</a>

        @can('cms-blog-update')
          <a class="btn btn-sm btn-primary font-weight-bold" href="@url('/admin/cms/blog/create?blog_id='){{$post->id}}">Edit</a>
        @endcan

        @can('cms-blog-delete')
          <form action="/api/cms/blog/{{$post->id}}/delete" method="POST"
            style="display: inline;"
            onSubmit="return confirm('Are you sure you want to delete this post?')">
            @csrf
            <button class="btn btn-danger btn-sm font-weight-bold" type="submit" >Delete</button>
          </form>
        @endcan
      </td>
    </tr>
  @endforeach
@end_dashboard_table({!! $data->paginate !!})
