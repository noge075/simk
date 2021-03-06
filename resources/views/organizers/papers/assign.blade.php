@extends('organizers.dashboard')

@section('content')
  <div class="row">
        <div class="col-md-10">
            <div class="panel panel-default">
                <div class="panel-heading">
                  Submission ID: {{ $submission->id }}
                </div>
                <div class="panel-body">
                  @include("partials.singlepaper_content")
                </div>
            </div>
        </div>
        <div class="col-md-10">
          <div class="panel panel-default">
              <div class="panel-heading">All Reviewers Registered</div>
              <div class="panel-body">
                <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($reviewers as $rev)
                    <tr>
                      <td>
                        {{ $rev->salutation . " " . $rev->last_name . " " . $rev->first_name }}
                        @if($rev->isReviewingPaper($submission->id))
                          <a href="{{ route('organizer.paper.detachReviewer', ['confUrl' => $conf->url, 'paperId' => $submission->id, 'userId' => $rev->id]) }}"><div class="label label-primary">reviewing</div></a>
                        @endif
                      </td>
                      <td>
                        @if(!$rev->isReviewingPaper($submission->id))
                          <a href="{{ route('organizer.paper.attachReviewer', ['confUrl' => $conf->url, 'paperId' => $submission->id, 'userId' => $rev->id]) }}"><div class="btn btn-sm btn-primary">Set as Paper Reviewer</div></a>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              </div>
          </div>
        </div>
  </div>
@endsection
