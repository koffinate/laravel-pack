@php
  $exception = kfnException();
@endphp
@if($exception->exist())
  <div class="alert alert-danger rounded-0 border-start-0 border-end-0">
    <div>{{ $exception->getCode() }} - {{ $exception->getStatusText() }}</div>
    <div>{!! $exception->getMessage() !!}</div>
  </div>
@endif
