@extends('conferences.dashboard')

@section('content')

  <div class="row">
    <div class="col-sm-12">  
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Registered Users</h3>
        </div>
        <div class="panel-body">
          <table class="table table-bordered">
            <thead>
              <tr class="text-center">
                <th>#</th>
                <th>Name</th>
                <th colspan="3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; ?>
              @foreach ($users as $usr)
                <tr>
                  <td>{{ $i }}</td>
                  <td>
                    {{ $usr->title . " " . $usr->last_name . " " . $usr->first_name }} 
                    @if ($usr->isAdmin()) 
                      @if ($user->isAdmin())
                        <a href="{{ route('enrolls.detachroles', [$conf->url, $usr->id, 'admin']) }}"><span class="label label-danger">Administrator</span></a>
                      @else 
                        <span class="label label-danger">Administrator</span>
                      @endif
                    @else                  
                      @if ($usr->isAuthoring($conf))
                        <a href="{{ route('enrolls.detachroles', [$conf->url, $usr->id, 'author']) }}"><span class="label label-warning">author</span></a>
                      @endif
                      @if ($usr->isReviewing($conf))
                        <a href="{{ route('enrolls.detachroles', [$conf->url, $usr->id, 'reviewer']) }}"><span class="label label-success">reviewer</span></a>
                      @endif
                      @if ($usr->isOrganizing($conf))
                        <a href="{{ route('enrolls.detachroles', [$conf->url, $usr->id, 'organizer']) }}"><span class="label label-info">organizer</span></a>
                      @endif
                    @endif
                  </td>
                  <td>
                    @if(!$usr->isAdmin())
                      @if (!$usr->isReviewing($conf))
                        <a href="{{ route('enrolls.attachroles', [$conf->url, $usr->id, 'reviewer'])}}" class="btn btn-success btn-xs">Set Reviewer</a>
                      @endif 
                      @if (!$usr->isOrganizing($conf))
                        <a href="{{ route('enrolls.attachroles', [$conf->url, $usr->id, 'organizer'])}}" class="btn btn-info btn-xs">Set Organizer</a>
                      @endif
                      @if (!$usr->isAuthoring($conf))
                        <a href="{{ route('enrolls.attachroles', [$conf->url, $usr->id, 'author'])}}" class="btn btn-warning btn-xs">Set Author</a>
                      @endif
                      @if ($user->isAdmin())
                        <a href="{{ route('enrolls.attachroles', [$conf->url, $usr->id, 'admin'])}}" class="btn btn-danger btn-xs">Set Admin</a>
                      @endif
                    @endif
                  </td>
                </tr>
                <?php $i++; ?>
              @endforeach
            </tbody>
          </table>
          <center>
            <div class="pagination"> {!! $users->render() !!}</div>
          </center>
        </div>
      </div> 
    </div>
  </div>    
@endsection