@extends('users.home.index')

@section('content')
  <div class="row">
        <div class="col-md-10">
        <div class="panel panel-default col-md-offset-2">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Upload Paper</h4>
                </div>
            </div>
            <div class="panel-body">
                   <form class="form form-vertical" role="form" method="POST" action="{{ route('test.addPaper.submit', $conf->url) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
                        <label>Title</label>

                        <div class="controls">
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}">

                            @if ($errors->has('title'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('title') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="form-group{{ $errors->has('abstract') ? ' has-error' : '' }}">
                            <label>Abstract</label>
                            <div class="controls">
                                <textarea class="form-control" name="abstract">{{ old('abstract') }}</textarea>

                                @if ($errors->has('abstract'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('abstract') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="form-group{{ $errors->has('keywords') ? ' has-error' : '' }}">
                            <label>Keywords</label>
                            <div class="controls">
                                <textarea class="form-control" name="keywords">{{ old('keywords') }}</textarea>
                                <span class="help-block">
                                    @if ($errors->has('keywords'))
                                       <strong>{{ $errors->first('keywords') }}</strong>
                                    @else
                                        <i>separate keywords by comma; e.g: Clean Energy, New Energy</i>
                                    @endif
                                </span>
                            </div>
                        <div>
                    </div>
                    <div class="control-group">
                        <div class="form-group{{ $errors->has('paper') ? ' has-error' : '' }}">
                            <label>File Upload
                                <br>
                            </label>
                            <div class="controls">
                                <input type="file" class="form-control input-sm" name="paper">
                                @if ($errors->has('paper'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('paper') }}</strong>
                                    </span>
                                @else
                                    <span class="help-block">
                                        <strong>Please upload file with .doc / .docx extension only.</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="control-group">
                        <label></label>
                        <div class="controls">
                            <button type="submit" class="btn btn-primary pull-right">Upload Paper</button>
                        </div>
                    </div
                </form>
        <!--/panel content-->
    </div>
</div>
@endsection
