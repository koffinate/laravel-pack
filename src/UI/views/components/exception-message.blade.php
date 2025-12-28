@php
  $exception = kfnException();
@endphp
@if($exception->exist())
  <div style="border:1px solid #f8285a; border-left:none; border-right:none; padding:1em; margin-top:.5em; margin-bottom:.5em; background-color:#ffeef3; color:#f8285a;">
    <div>{{ $exception->getCode() }} - {{ $exception->getStatusText() }}</div>
    <div>{!! $exception->getMessage() !!}</div>
  </div>
@endif
