@extends(adminTheme().'layouts.app')

@section('title')
<title>{{ websiteTitle('Accounts Dashboard') }}</title>
@endsection

@section('contents')
@include(adminTheme().'alerts')

<div class="flex-grow-1" style="padding:20px 20px 30px;">
    @include('erp-accounts::partials.dashboard-widget')
</div>

@endsection
