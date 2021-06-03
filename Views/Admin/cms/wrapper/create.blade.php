@php
    use Rapyd\Model\CmsContentWrapper;

    $wrapper = false;

    if(request('wrapper_id')) {
        $wrapper = CmsContentWrapper::find(request('wrapper_id'));
    }
@endphp

  <h1>Add wrapper</h1>

  <form action={{ $wrapper ? "/api/cms/wrapper/$wrapper->id/update" : "/api/cms/wrapper/store"}} method="POST">
      @csrf
      <div class="row">
          {{-- Left Side --}}
          <div class="col-md-9 col-sm-12">
              <div id="post_body">
                  {{-- wrapper Description --}}
                  <div class="form-group">
                      <label for="description">Description</label>
                      <input type="text" class="form-control" id="description" name='description'
                          value="{{$wrapper->description ?? old('description')}}" required>
                  </div>

                  {{-- wrapper Description --}}
                  <div class="form-group">
                      <label for="blade_path">Blade Path</label>
                      <input type="text" class="form-control" id="blade_path" name='blade_path'
                          value="{{$wrapper->blade_path ?? old('blade_path')}}" required>
                  </div>

                  <input id="save_post" type='submit' class='btn btn-primary' value='Save Changes'>
              </div>
          </div>
      </div>
  </form>

