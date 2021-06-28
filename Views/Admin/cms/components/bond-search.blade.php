@php
  $search_state = '';
  $search_query = '';

  if(request('search')) {
    $query          = explode(' ', request('search'));
    $search_state   = $query[0];
    array_shift($query);
    $search_query   = implode(' ', $query);
  }
@endphp

<form id="searchBondForm">
  <div class="card @if(!request('search')) card-collapsed @endif">
    <div class="card-header">
      <h3 class="card-title">Search Bonds</h3>
      <div class="card-options">
        <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-down"></i></a>
      </div>
    </div>
    <div class="card-body row">
      <div class="col-8">
        <div class="input-group">
            <select class="form-control rounded" 
              placeholder="State"  
              id="search_bonds_state"
              name="state" 
              style="max-width: 200px;"
              required
              >
              <option value="">Select State</option>
              @foreach ($states as $state)
                  <option value="{{$state->full}}" @if($search_state === $state->full) selected @endif>{{ $state->full }}</option>
              @endforeach
            </select>
            <input type="text" 
              class="form-control rounded mx-4" 
              placeholder="Search"
              id="search_bonds_query"
              name="search"
              value="{{$search_query}}"
              required
            />
            <button type="submit" class="btn btn-primary btn-sm font-weight-bold" id="search_bonds">Search</button>
        </div>
        <div class="col-4"></div>
      </div>
      @if($search_data)
        {{-- Results --}}
        <div class="col-12">
          @dashboard_table('Bond_ID, Description, Obligee, Limit,')
              @foreach($search_data as $bond)
                @php
                  $bond = $bond->indexable;
                @endphp
                <tr style="font-size: 12px;">
                  <td>{{$bond->id}}</td>
                  <td>
                    @if (strlen("{$bond->state_initial} - {$bond->description }") > 50)
                      {{ substr("{$bond->state_initial} - {$bond->description }", 0,50)."..." }}
                    @else
                      {{ $bond->state_initial }} - {{ $bond->description }}
                    @endif
                  </td>
                  <td>
                    {{ strlen($bond->obligee->name) > 40 ? substr($bond->obligee->name, 0,40)."..." : $bond->obligee->name}}
                  </td>
                  <td>
                    @php
                      $bond_limit = $bond->get_limit;
                    @endphp

                    {{-- ONE LIMIT (NON-CUSTOM) --}}
                    @if(count($bond->limits) == 1 && $bond_limit->first()->is_custom == 0)
                      ${{number_format($bond_limit->first()->amount)}}
                    {{-- ONE LIMIT (IS-CUSTOM) --}}
                    @elseif($bond_limit->first()->is_custom == 1)
                      ${{ number_format($bond_limit->first()->custom_min) }} -
                      ${{ number_format($bond_limit->first()->custom_max) }} (Custom)
                    {{-- MULTIPLE STAGGERED LIMIT --}}
                    @elseif($bond_limit->count() > 1 && $bond_limit->first()->is_custom == 0)
                      ${{ number_format($bond_limit->first()->amount) }} -
                      ${{ number_format($bond_limit->last()->amount) }}
                    @endif
                  </td>
                </tr>
              @endforeach
          @end_dashboard_table
        </div>
      @endif
    </div>
  </div>
</form>

<script>
  document.getElementById('searchBondForm').addEventListener('submit', searchBonds)

  // Search Bonds
  function searchBonds(e) {
    e.preventDefault()
    const searchState = document.getElementById('search_bonds_state').value;
    const searchQuery = document.getElementById('search_bonds_query').value;
    const searchUrl   = encodeURI(`/admin/cms/page/create?search=${searchState} ${searchQuery}`);
    window.location   = searchUrl;
  }
</script>