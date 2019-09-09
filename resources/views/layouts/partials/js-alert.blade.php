@if(request('ftype'))
    <div role="alert" class="alert alert-{{ request('ftype') }} alert-{{ request('fimportant') ? 'important' : '' }}">
        <button type="button" data-dismiss="alert" aria-hidden="true" class="close">×</button>
        {{ request('fmessage') }}
    </div>
@endif
