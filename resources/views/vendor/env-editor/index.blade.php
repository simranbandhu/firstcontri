@php($package='env-editor')
@php($translatePrefix='env-editor::env-editor.')

@extends(config("$package.layout"))
@push('documentTitle')
    <i class="fas fa-cog" aria-hidden="true"></i>
    {{trans('env-editor::env-editor.menuTitle')}}
@endpush


@section('content')
    <div id="env-editor">
        <div id="env-alerts"></div>

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#current-env" role="tab">{{__($translatePrefix.'views.tabTitles.currentEnv')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#backup-env" role="tab">{{__($translatePrefix.'views.tabTitles.backup')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#upload-env" role="tab">{{__($translatePrefix.'views.tabTitles.upload')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/panel/advanced-config') }}" role="tab">Advanced Config ⤻</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/panel/diagnose') }}" role="tab">Diagnosis ⤻</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/panel/phpinfo') }}" role="tab">PHP Info ⤻</a>
            </li>
            <li class="nav-item ml-auto">
                <env-editor-config-actions></env-editor-config-actions>
            </li>
        </ul>

	@if(env('NOTIFY_EVENTS') === false)
<br><br>
<a style="color:#ffbb39; font-weight:300; font-size:120%;">You currently have Event Notifications disabled. To get notified about polls, possible security vulnerabilities or important news, change the setting <code>NOTIFY_EVENTS</code> below to <code>true</code>. If you enable this and an event is happening, a small text will pop up on your Admin Panel which will only be visible for admins.</a>
	@endif
<?php if(strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false)echo "<br><br><p style=\"color:tomato; font-weight:300; font-size:120%;\">You appear to be using NGINX. Some features of the Config Editor do not work on NGINX based servers. You can use the alternative Config Editor <a href=" . url()->current() . "/../../panel/env>here</a>.</p>"; ?>

        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active p-3" id="current-env" role="tabpanel" aria-labelledby="nav-home-tab">
                <env-main-tab></env-main-tab>
            </div>
            <div class="tab-pane fade p-3" id="backup-env" role="tabpanel" aria-labelledby="nav-profile-tab">
                <env-editor-backups></env-editor-backups>
            </div>
            <div class="tab-pane fade p-3" id="upload-env" role="tabpanel" aria-labelledby="nav-contact-tab">
                <env-file-upload></env-file-upload>
            </div>
        </div>

        <env-keys-modal ref="keysModal"></env-keys-modal>
    </div>




@stop
@include('env-editor::components._itemModal')
@include('env-editor::components._currentEnv')
@include('env-editor::components._upload')
@include('env-editor::components._backup')
@include('env-editor::components._configActions')
@push('scripts')
    <script>
        window.envEventBus = new Vue();
        const envAlert = ($type, $text) => {
            let alert =
                '<div id="__id__" class="alert alert-__type__ alert-dismissible fade show" role="alert">' +
                '  <div>__text__</div>' +
                '  <button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '    <span aria-hidden="true">&times;</span>' +
                '  </button>' +
                '</div>';
            let $id = 'env-alert_' + Date.now();
            let $html = alert.replace('__type__', $type).replace('__text__', $text).replace('__id__', $id);
            $('#env-alerts').append($html);
            setTimeout(() => {
                $('#' + $id).alert('close')
            }, 3000)
        };

        window.envClient = (endpoint, customConfig) => {
            const data= customConfig && customConfig.data
            let headers = {
                'Accept': 'application/json',
                "X-CSRF-Token": '{{csrf_token()}}'
            }
            if (data) {
                headers['Content-Type'] = 'application/json'
                customConfig.body = JSON.stringify(customConfig.data)
            }

            const config = {
                ...customConfig,
                headers: headers,
            }

            return window
                .fetch(endpoint, config)
                .then(async response => {
                    const data = await response.json()
                    if (response.ok) {
                        return data
                    }
                    envAlert('danger', data.message);
                    return Promise.reject(data)
                })
        };

        const dotEnv = new Vue({
            el: '#env-editor',
            components: {
                'env-main-tab': itemsWrapper,
                'env-keys-modal': itemsModal,
                'env-file-upload': fileUpload,
                'env-editor-backups': backUps,
                'env-editor-config-actions': configActions
            }
        })
    </script>
@endpush

@extends('layouts.sidebar')
