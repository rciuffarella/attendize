{{-- @todo Rewrite the JS for choosing event bg images and colours. --}}
@extends('Shared.Layouts.Master')

@section('title')
    @parent
    @lang("Event.customize_event")
@stop

@section('top_nav')
    @include('ManageEvent.Partials.TopNav')
@stop

@section('menu')
    @include('ManageEvent.Partials.Sidebar')
@stop

@section('page_title')
    <i class="ico-cog mr5"></i>
    @lang("Event.customize_event")
@stop

@section('page_header')
    <style>
        .page-header {
            display: none;
        }
    </style>
@stop

@section('head')
    {!! Html::script('https://maps.googleapis.com/maps/api/js?libraries=places&key='.config("attendize.google_maps_geocoding_key")) !!}
    {!! Html::script('vendor/geocomplete/jquery.geocomplete.min.js') !!}
    <script>
        $(function () {

            $("input[name='organiser_fee_percentage']").TouchSpin({
                min: 0,
                max: 100,
                step: 0.1,
                decimals: 2,
                verticalbuttons: true,
                forcestepdivisibility: 'none',
                postfix: '%',
                buttondown_class: "btn btn-link",
                buttonup_class: "btn btn-link",
                postfix_extraclass: "btn btn-link"
            });
            $("input[name='organiser_fee_fixed']").TouchSpin({
                min: 0,
                max: 100,
                step: 0.01,
                decimals: 2,
                verticalbuttons: true,
                postfix: '{{$event->currency->symbol_left}}',
                buttondown_class: "btn btn-link",
                buttonup_class: "btn btn-link",
                postfix_extraclass: "btn btn-link"
            });

            /* Affiliate generator */
            $('#affiliateGenerator').on('keyup', function () {
                var text = $(this).val().replace(/\W/g, ''),
                        referralUrl = '{{$event->event_url}}?ref=' + text;

                $('#referralUrl').toggle(text !== '');
                $('#referralUrl input').val(referralUrl);
            });

            /* Background selector */
            $('.bgImage').on('click', function (e) {
                $('.bgImage').removeClass('selected');
                $(this).addClass('selected');
                $('input[name=bg_image_path_custom]').val($(this).data('src'));

                var replaced = replaceUrlParam('{{route('showEventPagePreview', ['event_id'=>$event->id])}}', 'bg_img_preview', $('input[name=bg_image_path_custom]').val());
                document.getElementById('previewIframe').src = replaced;
                e.preventDefault();
            });

            /* Background color */
            $('input[name=bg_color]').on('change', function (e) {
                var replaced = replaceUrlParam('{{route('showEventPagePreview', ['event_id'=>$event->id])}}', 'bg_color_preview', $('input[name=bg_color]').val().substring(1));
                document.getElementById('previewIframe').src = replaced;
                e.preventDefault();
            });

            $('#bgOptions .panel').on('shown.bs.collapse', function (e) {
                var type = $(e.currentTarget).data('type');
                console.log(type);
                $('input[name=bg_type]').val(type);
            });

            $('input[name=bg_image_path], input[name=bg_color]').on('change', function () {
                //showMessage('Uploading...');
                //$('.customizeForm').submit();
            });

            /* Color picker */
            $('.colorpicker').minicolors();

            $('#ticket_design .colorpicker').on('change', function (e) {
                var borderColor = $('input[name="ticket_border_color"]').val();
                var bgColor = $('input[name="ticket_bg_color"]').val();
                var textColor = $('input[name="ticket_text_color"]').val();
                var subTextColor = $('input[name="ticket_sub_text_color"]').val();

                $('.ticket').css({
                    'border': '1px solid ' + borderColor,
                    'background-color': bgColor,
                    'color': subTextColor,
                    'border-left-color': borderColor
                });
                $('.ticket h4').css({
                    'color': textColor
                });
                $('.ticket .logo').css({
                    'border-left': '1px solid ' + borderColor,
                    'border-bottom': '1px solid ' + borderColor
                });
                $('.ticket .barcode').css({
                    'border-right': '1px solid ' + borderColor,
                    'border-bottom': '1px solid ' + borderColor,
                    'border-top': '1px solid ' + borderColor
                });

            });

            $('#enable_offline_payments').change(function () {
                $('.offline_payment_details').toggle(this.checked);
            }).change();

            // Gestione edit zona posti
            $(document.body).on('click', '.edit-seat-zone', function (e) {
                e.preventDefault();
                var $btn = $(this);
                var routeUpdate = $btn.data('route-update');

                $('#editSeatZoneForm').attr('action', routeUpdate).show();
                $('#edit_zone_id').val($btn.data('id'));
                $('#edit_zone_name').val($btn.data('name'));
                $('#edit_zone_color').val($btn.data('color'));
                $('#edit_ticket_id').val($btn.data('ticket-id'));
                $('#edit_position_x').val($btn.data('position-x'));
                $('#edit_position_y').val($btn.data('position-y'));
                $('#edit_rows').val($btn.data('rows') || '');
                $('#edit_cols').val($btn.data('cols') || '');
                $('#edit_start_row_label').val($btn.data('start-row') || '');
                $('#edit_start_col_number').val($btn.data('start-col') || '');
            });

            // Drag & drop riquadri zona nella mappa admin
            (function () {
                var $container = $('#seat-map-admin');

                // L'anteprima esiste solo nella tab "Mappa posti"
                if (!$container.length) {
                    return;
                }

                var dragging = null;
                var offsetX = 0;
                var offsetY = 0;

                $(document.body).on('mousedown', '.seat-zone-admin', function (e) {
                    dragging = $(this);

                    // Quando clicco sul riquadro, considero automaticamente quella zona come "in modifica"
                    var zoneId = dragging.data('id');
                    var $rowBtn = $('.edit-seat-zone[data-id=\"' + zoneId + '\"]');
                    if ($rowBtn.length) {
                        var routeUpdate = $rowBtn.data('route-update');
                        $('#editSeatZoneForm').attr('action', routeUpdate).show();
                        $('#edit_zone_id').val($rowBtn.data('id'));
                        $('#edit_zone_name').val($rowBtn.data('name'));
                        $('#edit_zone_color').val($rowBtn.data('color'));
                        $('#edit_ticket_id').val($rowBtn.data('ticket-id'));
                        $('#edit_position_x').val($rowBtn.data('position-x'));
                        $('#edit_position_y').val($rowBtn.data('position-y'));
                        $('#edit_rows').val($rowBtn.data('rows') || '');
                        $('#edit_cols').val($rowBtn.data('cols') || '');
                        $('#edit_start_row_label').val($rowBtn.data('start-row') || '');
                        $('#edit_start_col_number').val($rowBtn.data('start-col') || '');
                    }

                    var containerOffset = $container.offset();
                    var pos = dragging.position(); // posizione relativa al container

                    // distanza mouse -> angolo sinistro del riquadro, nel sistema di riferimento del container
                    offsetX = (e.pageX - containerOffset.left) - pos.left;
                    offsetY = (e.pageY - containerOffset.top) - pos.top;

                    e.preventDefault();
                });

                $(document).on('mouseup', function () {
                    dragging = null;
                });

                $(document).on('mousemove', function (e) {
                    if (!dragging) {
                        return;
                    }

                    var containerOffset = $container.offset();
                    var relX = e.pageX - containerOffset.left;
                    var relY = e.pageY - containerOffset.top;

                    var left = relX - offsetX;
                    var top = relY - offsetY;

                    // Limita il movimento all'interno del contenitore
                    left = Math.max(0, Math.min(left, $container.width() - dragging.outerWidth()));
                    top = Math.max(0, Math.min(top, $container.height() - dragging.outerHeight()));

                    dragging.css({left: left, top: top, position: 'absolute'});

                    var xPercent = (left / $container.width()) * 100;
                    var yPercent = (top / $container.height()) * 100;
                    var zoneId = dragging.data('id');

                    // Se stiamo modificando questa zona, aggiorna anche i campi del form
                    if ($('#edit_zone_id').val() == zoneId) {
                        $('#edit_position_x').val(xPercent.toFixed(1));
                        $('#edit_position_y').val(yPercent.toFixed(1));
                    }
                });
            })();
        });


    </script>

    <style type="text/css">
        .bootstrap-touchspin-postfix {
            background-color: #ffffff;
            color: #333;
            border-left: none;
        }

        .bgImage {
            cursor: pointer;
        }

        .bgImage.selected {
            outline: 4px solid #0099ff;
        }
    </style>
    <script>
        $(function () {

            var hash = document.location.hash;
            var prefix = "tab_";
            if (hash) {
                $('.nav-tabs a[href=' + hash + ']').tab('show');
            }

            $(window).on('hashchange', function () {
                var newHash = location.hash;
                if (typeof newHash === undefined) {
                    $('.nav-tabs a[href=' + '#general' + ']').tab('show');
                } else {
                    $('.nav-tabs a[href=' + newHash + ']').tab('show');
                }

            });

            $('.nav-tabs a').on('shown.bs.tab', function (e) {
                window.location.hash = e.target.hash;
            });

        });


    </script>

@stop


@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- tab -->
            <ul class="nav nav-tabs">
                <li data-route="{{route('showEventCustomizeTab', ['event_id' => $event->id, 'tab' => 'general'])}}"
                    class="{{($tab == 'general' || !$tab) ? 'active' : ''}}"><a href="#general" data-toggle="tab">@lang("basic.general")</a>
                </li>
                <li data-route="{{route('showEventCustomizeTab', ['event_id' => $event->id, 'tab' => 'design'])}}"
                    class="{{$tab == 'design' ? 'active' : ''}}"><a href="#design" data-toggle="tab">@lang("basic.event_page_design")</a></li>
                <li data-route="{{route('showEventCustomizeTab', ['event_id' => $event->id, 'tab' => 'order_page'])}}"
                    class="{{$tab == 'order_page' ? 'active' : ''}}"><a href="#order_page" data-toggle="tab">@lang("basic.order_form")</a></li>

                <li data-route="{{route('showEventCustomizeTab', ['event_id' => $event->id, 'tab' => 'social'])}}"
                    class="{{$tab == 'social' ? 'active' : ''}}"><a href="#social" data-toggle="tab">@lang("basic.social")</a></li>
                <li data-route="{{route('showEventCustomizeTab', ['event_id' => $event->id, 'tab' => 'affiliates'])}}"
                    class="{{$tab == 'affiliates' ? 'active' : ''}}"><a href="#affiliates"
                                                                        data-toggle="tab">@lang("basic.affiliates")</a></li>
                <li data-route="{{route('showEventCustomizeTab', ['event_id' => $event->id, 'tab' => 'fees'])}}"
                    class="{{$tab == 'fees' ? 'active' : ''}}"><a href="#fees" data-toggle="tab">@lang("basic.service_fees")</a></li>
                <li data-route="{{route('showEventCustomizeTab', ['event_id' => $event->id, 'tab' => 'ticket_design'])}}"
                    class="{{$tab == 'ticket_design' ? 'active' : ''}}"><a href="#ticket_design" data-toggle="tab">@lang("basic.ticket_design")</a></li>
                <li data-route="{{route('showEventCustomizeTab', ['event_id' => $event->id, 'tab' => 'seat_map'])}}"
                    class="{{$tab == 'seat_map' ? 'active' : ''}}"><a href="#seat_map" data-toggle="tab">@lang("basic.seat_map")</a></li>
                <li data-route="{{route('showEventCustomizeTab', ['event_id' => $event->id, 'tab' => 'dates'])}}"
                    class="{{$tab == 'dates' ? 'active' : ''}}"><a href="#dates" data-toggle="tab">Date e orari</a></li>
            </ul>
            <!--/ tab -->
            <!-- tab content -->
            <div class="tab-content panel">
                <div class="tab-pane {{($tab == 'general' || !$tab) ? 'active' : ''}}" id="general">
                    @include('ManageEvent.Partials.EditEventForm', ['event'=>$event, 'organisers'=>\Auth::user()->account->organisers])
                </div>

                <div class="tab-pane {{$tab == 'affiliates' ? 'active' : ''}}" id="affiliates">

                    <h4>@lang("Affiliates.affiliate_tracking")</h4>

                    <div class="well">
                        @lang("Affiliates.affiliate_tracking_text")

                        <br><br>

                        <input type="text" id="affiliateGenerator" name="affiliateGenerator" class="form-control"/>

                        <div style="display:none; margin-top:10px; " id="referralUrl">
                            <input onclick="this.select();" type="text" name="affiliateLink" class="form-control"/>
                        </div>
                    </div>

                    @if($event->affiliates->count())
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>@lang("Affiliates.affiliate_name")</th>
                                    <th>@lang("Affiliates.visits_generated")</th>
                                    <th>@lang("Affiliates.ticket_sales_generated")</th>
                                    <th>@lang("Affiliates.sales_volume_generated")</th>
                                    <th>@lang("Affiliates.last_referral")</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach($event->affiliates as $affiliate)
                                    <tr>
                                        <td>{{ $affiliate->name }}</td>
                                        <td>{{ $affiliate->visits }}</td>
                                        <td>{{ $affiliate->tickets_sold }}</td>
                                        <td>{{ money($affiliate->sales_volume, $event->currency) }}</td>
                                        <td>{{ $affiliate->updated_at->format(config("attendize.default_datetime_format")) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            @lang("Affiliates.no_affiliate_referrals_yet")
                        </div>
                    @endif


                </div>
                <div class="tab-pane {{$tab == 'social' ? 'active' : ''}}" id="social">
                    <div class="well hide"> <?php /*Seems like unfinished feature -> not translating*/ ?>
                        <h5>The following short codes are available for use:</h5>
                        Display the event's public URL: <code>[event_url]</code><br>
                        Display the organiser's name: <code>[organiser_name]</code><br>
                        Display the event title: <code>[event_title]</code><br>
                        Display the event description: <code>[event_description]</code><br>
                        Display the event start date & time: <code>[event_start_date]</code><br>
                        Display the event end date & time: <code>[event_end_date]</code>
                    </div>

                    {!! Form::model($event, array('url' => route('postEditEventSocial', ['event_id' => $event->id]), 'class' => 'ajax ')) !!}

                    <h4>@lang("Social.social_settings")</h4>

                    <div class="form-group hide">

                        {!! Form::label('social_share_text', trans("Social.social_share_text"), array('class'=>'control-label ')) !!}

                        {!!  Form::textarea('social_share_text', $event->social_share_text, [
                            'class' => 'form-control',
                            'rows' => 4
                        ])  !!}
                        <div class="help-block">
                            @lang("Social.social_share_text_help")
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang("Social.share_buttons_to_show")</label>
                        <br>

                        <div class="custom-checkbox mb5">
                            {!! Form::checkbox('social_show_facebook', 1, $event->social_show_facebook, ['id' => 'social_show_facebook', 'data-toggle' => 'toggle']) !!}
                            {!! Form::label('social_show_facebook', trans("Social.facebook")) !!}
                        </div>
                        <div class="custom-checkbox mb5">

                            {!! Form::checkbox('social_show_twitter', 1, $event->social_show_twitter, ['id' => 'social_show_twitter', 'data-toggle' => 'toggle']) !!}
                            {!! Form::label('social_show_twitter', trans("Social.twitter")) !!}

                        </div>

                        <div class="custom-checkbox mb5">
                            {!! Form::checkbox('social_show_email', 1, $event->social_show_email, ['id' => 'social_show_email', 'data-toggle' => 'toggle']) !!}
                            {!! Form::label('social_show_email', trans("Social.email")) !!}
                        </div>
                        <div class="custom-checkbox mb5">
                            {!! Form::checkbox('social_show_linkedin', 1, $event->social_show_linkedin, ['id' => 'social_show_linkedin', 'data-toggle' => 'toggle']) !!}
                            {!! Form::label('social_show_linkedin', trans("Social.linkedin")) !!}
                        </div>
                        <div class="custom-checkbox">

                            {!! Form::checkbox('social_show_whatsapp', 1, $event->social_show_whatsapp, ['id' => 'social_show_whatsapp', 'data-toggle' => 'toggle']) !!}
                            {!! Form::label('social_show_whatsapp', trans("Social.whatsapp")) !!}


                        </div>
                    </div>

                    <div class="panel-footer mt15 text-right">
                        {!! Form::submit(trans("basic.save_changes"), ['class'=>"btn btn-success"]) !!}
                    </div>

                    {!! Form::close() !!}

                </div>
                <div class="tab-pane scale_iframe {{$tab == 'design' ? 'active' : ''}}" id="design">

                    <div class="row">
                        <div class="col-sm-6">

                            {!! Form::open(array('url' => route('postEditEventDesign', ['event_id' => $event->id]), 'files'=> true, 'class' => 'ajax customizeForm')) !!}

                            {!! Form::hidden('bg_type', $event->bg_type) !!}

                            <h4>@lang("Design.background_options")</h4>

                            <div class="panel-group" id="bgOptions">

                                <div class="panel panel-default" data-type="color">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#bgOptions" href="#bgColor"
                                               class="{{($event->bg_type == 'color') ? '' : 'collapsed'}}">
                                                <span class="arrow mr5"></span> @lang("Design.use_a_colour_for_the_background")
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="bgColor"
                                         class="panel-collapse {{($event->bg_type == 'color') ? 'in' : 'collapse'}}">
                                        <div class="panel-body">
                                            {!! Form::text('bg_color', $event->bg_color, ['class' => 'colorpicker form-control']) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="panel panel-default" data-type="image">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#bgOptions" href="#bgImage"
                                               class="{{($event->bg_type == 'image') ? '' : 'collapsed'}}">
                                                <span class="arrow mr5"></span> @lang("Design.select_from_available_images")
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="bgImage"
                                         class="panel-collapse {{($event->bg_type == 'image') ? 'in' : 'collapse'}}">
                                        <div class="panel-body">
                                            @foreach($available_bg_images_thumbs as $bg_image)

                                                <img data-3="{{str_replace('/thumbs', '', $event->bg_image_path)}}"
                                                     class="img-thumbnail ma5 bgImage {{ ($bg_image === str_replace('/thumbs', '', $event->bg_image_path) ? 'selected' : '') }}"
                                                     style="width: 120px;" src="{{asset($bg_image)}}"
                                                     data-src="{{str_replace('/thumbs', '', substr($bg_image,1))}}"/>

                                            @endforeach

                                            {!! Form::hidden('bg_image_path_custom', ($event->bg_type == 'image') ? $event->bg_image_path : '') !!}
                                        </div>
                                            <a class="btn btn-link" href="https://pixabay.com?ref=attendize" title="PixaBay Free Images">
                                                @lang("Design.images_provided_by_pixabay")
                                            </a>
                                    </div>
                                </div>

                            </div>
                            <div class="panel-footer mt15 text-right">
                                <span class="uploadProgress" style="display:none;"></span>
                                {!! Form::submit(trans("basic.save_changes"), ['class'=>"btn btn-success"]) !!}
                            </div>

                            <div class="panel-footer ar hide">
                                {!! Form::button(trans("basic.cancel"), ['class'=>"btn modal-close btn-danger",'data-dismiss'=>'modal']) !!}
                                {!! Form::submit(trans("basic.save_changes"), ['class'=>"btn btn-success"]) !!}
                            </div>

                            {!! Form::close() !!}

                        </div>
                        <div class="col-sm-6">
                            <h4>@lang("Design.event_page_preview")</h4>

                            <div class="iframe_wrap" style="overflow:hidden; height: 600px; border: 1px solid #ccc;">
                                <iframe id="previewIframe"
                                        src="{{route('showEventPagePreview', ['event_id'=>$event->id])}}"
                                        frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%"
                                        width="100%">
                                </iframe>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="tab-pane {{$tab == 'fees' ? 'active' : ''}}" id="fees">
                    {!! Form::model($event, array('url' => route('postEditEventFees', ['event_id' => $event->id]), 'class' => 'ajax')) !!}
                    <h4>@lang("Fees.organiser_fees")</h4>

                    <div class="well">
                        {!! @trans("Fees.organiser_fees_text") !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('organiser_fee_percentage', trans("Fees.service_fee_percentage"), array('class'=>'control-label required')) !!}
                        {!!  Form::text('organiser_fee_percentage', $event->organiser_fee_percentage, [
                            'class' => 'form-control',
                            'placeholder' => trans("Fees.service_fee_percentage_placeholder")
                        ])  !!}
                        <div class="help-block">
                            {!! @trans("Fees.service_fee_percentage_help") !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('organiser_fee_fixed', trans("Fees.service_fee_fixed_price"), array('class'=>'control-label required')) !!}
                        {!!  Form::text('organiser_fee_fixed', null, [
                            'class' => 'form-control',
                            'placeholder' => trans("Fees.service_fee_fixed_price_placeholder")
                        ])  !!}
                        <div class="help-block">
                            {!! @trans("Fees.service_fee_fixed_price_help", ["cur"=>$event->currency_symbol]) !!}
                        </div>
                    </div>
                    <div class="panel-footer mt15 text-right">
                        {!! Form::submit(trans("basic.save_changes"), ['class'=>"btn btn-success"]) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="tab-pane" id="social"> <?php /* Seems like another unused section (duplicate id 'social') */ ?>
                    <h4>Social Settings</h4>

                    <div class="form-group">
                        <div class="checkbox custom-checkbox">
                            {!! Form::label('event_page_show_map', 'Show map on event page?', array('id' => 'customcheckbox', 'class'=>'control-label')) !!}
                            {!! Form::checkbox('event_page_show_map', 1, false) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('event_page_show_social_share', 'Show social share buttons?', array('class'=>'control-label')) !!}
                        {!! Form::checkbox('event_page_show_social_share', 1, false) !!}
                    </div>

                </div>

                <div class="tab-pane {{$tab == 'order_page' ? 'active' : ''}}" id="order_page">
                    {!! Form::model($event, array('url' => route('postEditEventOrderPage', ['event_id' => $event->id]), 'class' => 'ajax ')) !!}
                    <h4>@lang("Order.order_page_settings")</h4>

                    <div class="form-group">
                        {!! Form::label('pre_order_display_message', trans("Order.before_order"), array('class'=>'control-label ')) !!}

                        {!!  Form::textarea('pre_order_display_message', $event->pre_order_display_message, [
                            'class' => 'form-control',
                            'rows' => 4
                        ])  !!}
                        <div class="help-block">
                            @lang("Order.before_order_help")
                        </div>

                    </div>
                    <div class="form-group">
                        {!! Form::label('post_order_display_message', trans("Order.after_order"), array('class'=>'control-label ')) !!}

                        {!!  Form::textarea('post_order_display_message', $event->post_order_display_message, [
                            'class' => 'form-control',
                            'rows' => 4
                        ])  !!}
                        <div class="help-block">
                            @lang("Order.after_order_help")
                        </div>
                    </div>


                        <h4>@lang("Order.offline_payment_settings")</h4>
                        <div class="form-group">
                            <div class="custom-checkbox">
                                <input {{ $event->enable_offline_payments ? 'checked="checked"' : '' }} data-toggle="toggle" id="enable_offline_payments" name="enable_offline_payments" type="checkbox" value="1">
                                <label for="enable_offline_payments">@lang("Order.enable_offline_payments")</label>
                            </div>
                        </div>
                        <div class="offline_payment_details" style="display: none;">
                            {!! Form::textarea('offline_payment_instructions', $event->offline_payment_instructions, ['class' => 'form-control editable']) !!}
                            <div class="help-block">
                                @lang("Order.offline_payment_instructions")
                            </div>
                        </div>


                    <div class="panel-footer mt15 text-right">
                        {!! Form::submit(trans("basic.save_changes"), ['class'=>"btn btn-success"]) !!}
                    </div>

                    {!! Form::close() !!}

                </div>


                <div class="tab-pane {{$tab == 'ticket_design' ? 'active' : ''}}" id="ticket_design">
                    {!! Form::model($event, array('url' => route('postEditEventTicketDesign', ['event_id' => $event->id]), 'class' => 'ajax ')) !!}
                    <h4>@lang("Ticket.ticket_design")</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('ticket_border_color', trans("Ticket.ticket_border_color"), ['class'=>'control-label required ']) !!}
                                {!!  Form::input('text', 'ticket_border_color', old('ticket_border_color'),
                                                            [
                                                            'class'=>'form-control colorpicker',
                                                            'placeholder'=>'#000000'
                                                            ])  !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('ticket_bg_color', trans("Ticket.ticket_background_color"), ['class'=>'control-label required ']) !!}
                                {!!  Form::input('text', 'ticket_bg_color', old('ticket_bg_color'),
                                                            [
                                                            'class'=>'form-control colorpicker',
                                                            'placeholder'=>'#FFFFFF'
                                                            ])  !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('ticket_text_color', trans("Ticket.ticket_text_color"), ['class'=>'control-label required ']) !!}
                                {!!  Form::input('text', 'ticket_text_color', old('ticket_text_color'),
                                                            [
                                                            'class'=>'form-control colorpicker',
                                                            'placeholder'=>'#000000'
                                                            ])  !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('ticket_sub_text_color', trans("Ticket.ticket_sub_text_color"), ['class'=>'control-label required ']) !!}
                                {!!  Form::input('text', 'ticket_sub_text_color', old('ticket_border_color'),
                                                            [
                                                            'class'=>'form-control colorpicker',
                                                            'placeholder'=>'#000000'
                                                            ])  !!}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('is_1d_barcode_enabled', trans("Ticket.show_1d_barcode"), ['class' => 'control-label required']) !!}
                                {!! Form::select('is_1d_barcode_enabled', [1 => trans("basic.yes"), 0 => trans("basic.no")], $event->is_1d_barcode_enabled, ['class'=>'form-control']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md-12">
                            <h4>@lang("Ticket.ticket_preview")</h4>
                            @include('ManageEvent.Partials.TicketDesignPreview')
                        </div>
                    </div>
                    <div class="panel-footer mt15 text-right">
                        {!! Form::submit(trans("basic.save_changes"), ['class'=>"btn btn-success"]) !!}
                    </div>
                    {!! Form::close() !!}
                </div>

                <div class="tab-pane {{$tab == 'seat_map' ? 'active' : ''}}" id="seat_map">
                    <h4>@lang("basic.seat_map")</h4>
                    <p class="help-block">
                        Qui potrai configurare la piantina dei posti per questo evento (zone, posti e prezzi).<br>
                        Nella prima versione è solo una struttura dati: l’editor grafico verrà aggiunto in seguito.
                    </p>

                    @if($event->seatMaps->count())
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h5 class="panel-title">Piantine esistenti</h5>
                            </div>
                            <div class="panel-body">
                                <table class="table table-condensed">
                                    <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Ultima modifica</th>
                                        <th>Zone</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($event->seatMaps as $seatMap)
                                        <tr>
                                            <td>{{ $seatMap->name }}</td>
                                            <td>{{ $seatMap->updated_at ? $seatMap->updated_at->format(config('attendize.default_datetime_format')) : '' }}</td>
                                            <td>{{ $seatMap->zones->count() }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {!! Form::open(['url' => route('postEditEventSeatMap', ['event_id' => $event->id]), 'class' => 'ajax form']) !!}
                    <div class="form-group">
                        {!! Form::label('seat_map_name', 'Nome piantina', ['class' => 'control-label required']) !!}
                        {!! Form::text('seat_map_name', optional($event->seatMaps->first())->name ?: $event->title . ' - Piantina', ['class' => 'form-control', 'placeholder' => 'Es: Platea teatro']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('background_image_path', 'URL immagine piantina (opzionale)', ['class' => 'control-label']) !!}
                        {!! Form::text('background_image_path', optional($event->seatMaps->first())->background_image_path, ['class' => 'form-control', 'placeholder' => 'Es: https://.../piantina.png']) !!}
                        <p class="help-block">Inserisci l'URL assoluto o relativo dell'immagine da usare come sfondo sotto i pallini.</p>
                    </div>
                    <div class="form-group">
                        {!! Form::label('capacity', 'Capienza sala (numero massimo posti)', ['class' => 'control-label']) !!}
                        {!! Form::number('capacity', optional($event->seatMaps->first())->capacity, ['class' => 'form-control', 'min' => 1, 'max' => 10000, 'placeholder' => 'Es: 150']) !!}
                        <p class="help-block">Questo valore verrà usato per adattare le dimensioni dell&apos;area in cui posizionare le zone, sia in admin che nella pagina evento.</p>
                    </div>
                    <div class="panel-footer mt15 text-right">
                        {!! Form::submit(trans("basic.save_changes"), ['class'=>"btn btn-success"]) !!}
                    </div>
                    {!! Form::close() !!}

                    <hr>

                    <h4>Zone e generazione posti</h4>
                    @if(!$event->seatMaps->count())
                        <div class="alert alert-info">
                            Crea prima una piantina qui sopra, poi potrai aggiungere le zone e generare i posti.
                        </div>
                    @else
                        @php $currentMap = $event->seatMaps->first(); @endphp
                        @if($currentMap->zones->count())
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">Zone esistenti</h5>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-condensed">
                                        <thead>
                                        <tr>
                                            <th>Nome zona</th>
                                            <th>Colore</th>
                                            <th>Biglietto</th>
                                            <th>Numero posti</th>
                                            <th>Posizione X %</th>
                                            <th>Posizione Y %</th>
                                            <th>Azioni</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach($currentMap->zones as $zone): ?>
                                        <?php
                                            $rowsCollection = $zone->seats->groupBy('row_label')->sortKeys();
                                            $rowsCount = $rowsCollection->count();
                                            $maxCols = 0;
                                            foreach ($rowsCollection as $rowSeats) {
                                                $maxCols = max($maxCols, $rowSeats->count());
                                            }
                                        ?>
                                            <tr>
                                                <td>{{ $zone->name }}</td>
                                                <td>
                                                    <span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:{{ $zone->color ?: '#ccc' }};"></span>
                                                    <small>{{ $zone->color }}</small>
                                                </td>
                                                <td>{{ optional($zone->ticket)->title ?: '-' }}</td>
                                                <td>{{ $zone->seats->count() }}</td>
                                                <td>{{ $zone->position_x }}</td>
                                                <td>{{ $zone->position_y }}</td>
                                                <td>
                                                    <button type="button"
                                                            class="btn btn-xs btn-link edit-seat-zone"
                                                            data-id="{{ $zone->id }}"
                                                            data-route-update="{{ route('postUpdateSeatZone', ['event_id' => $event->id, 'zone_id' => $zone->id]) }}"
                                                            data-name="{{ $zone->name }}"
                                                            data-color="{{ $zone->color }}"
                                                            data-ticket-id="{{ $zone->ticket_id }}"
                                                            data-position-x="{{ $zone->position_x }}"
                                                            data-position-y="{{ $zone->position_y }}"
                                                            data-rows="{{ $rowsCount }}"
                                                            data-cols="{{ $maxCols }}"
                                                            data-start-row="{{ $zone->start_row_alpha ? chr($zone->start_row_alpha) : 'A' }}"
                                                            data-start-col="{{ $zone->start_col_num ?: 1 }}">
                                                        Modifica
                                                    </button>

                                                    {!! Form::open(['url' => route('postDeleteSeatZone', ['event_id' => $event->id, 'zone_id' => $zone->id]), 'class' => 'ajax', 'style' => 'display:inline']) !!}
                                                    <button type="submit"
                                                            class="btn btn-xs btn-link text-danger"
                                                            onclick="return confirm('Sei sicuro di voler cancellare questa zona? I posti non ancora assegnati verranno rimossi.');">
                                                        Elimina
                                                    </button>
                                                    {!! Form::close() !!}
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {!! Form::open(['url' => route('postCreateSeatZone', ['event_id' => $event->id]), 'class' => 'ajax form', 'id' => 'createSeatZoneForm']) !!}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('zone_name', 'Nome zona', ['class' => 'control-label required']) !!}
                                    {!! Form::text('zone_name', null, ['class' => 'form-control', 'placeholder' => 'Es: Platea centrale']) !!}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::label('zone_color', 'Colore', ['class' => 'control-label']) !!}
                                    {!! Form::text('zone_color', '#999999', ['class' => 'form-control colorpicker']) !!}
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    {!! Form::label('ticket_id', 'Tipo di biglietto collegato', ['class' => 'control-label']) !!}
                                    {!! Form::select('ticket_id', $event->tickets->pluck('title','id'), null, ['class' => 'form-control', 'placeholder' => 'Nessun collegamento']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::label('rows', 'Numero file', ['class' => 'control-label required']) !!}
                                    {!! Form::number('rows', 5, ['class' => 'form-control', 'min' => 1, 'max' => 50]) !!}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::label('cols', 'Posti per fila', ['class' => 'control-label required']) !!}
                                    {!! Form::number('cols', 8, ['class' => 'form-control', 'min' => 1, 'max' => 50]) !!}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::label('position_x', 'Posizione X %', ['class' => 'control-label']) !!}
                                    {!! Form::number('position_x', null, ['class' => 'form-control', 'min' => 0, 'max' => 100, 'step' => '0.1']) !!}
                                    <p class="help-block">0 = sinistra, 100 = destra (rispetto alla piantina).</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('position_y', 'Posizione Y %', ['class' => 'control-label']) !!}
                                    {!! Form::number('position_y', null, ['class' => 'form-control', 'min' => 0, 'max' => 100, 'step' => '0.1']) !!}
                                    <p class="help-block">0 = alto, 100 = basso.</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::label('start_row_label', 'Prima fila', ['class' => 'control-label']) !!}
                                    {!! Form::text('start_row_label', 'A', ['class' => 'form-control', 'maxlength' => 2]) !!}
                                    <p class="help-block">Es: A, B, C...</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::label('start_col_number', 'Primo posto n°', ['class' => 'control-label']) !!}
                                    {!! Form::number('start_col_number', 1, ['class' => 'form-control', 'min' => 0, 'max' => 9999]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer mt15 text-right">
                            {!! Form::submit('Crea zona e genera posti', ['class'=>"btn btn-primary"]) !!}
                        </div>
                        {!! Form::close() !!}

                        {{-- Form di modifica zona (nome/colore/biglietto, posizioni, righe/colonne, etichette) --}}
                        {!! Form::open(['url' => '#', 'class' => 'form mt20', 'id' => 'editSeatZoneForm', 'style' => 'display:none;']) !!}
                        {!! Form::hidden('zone_id', null, ['id' => 'edit_zone_id']) !!}
                        <h5>Modifica zona</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('edit_zone_name', 'Nome zona', ['class' => 'control-label required']) !!}
                                    {!! Form::text('zone_name', null, ['class' => 'form-control', 'id' => 'edit_zone_name']) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('edit_zone_color', 'Colore', ['class' => 'control-label']) !!}
                                    {!! Form::text('zone_color', null, ['class' => 'form-control colorpicker', 'id' => 'edit_zone_color']) !!}
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    {!! Form::label('edit_ticket_id', 'Tipo di biglietto collegato', ['class' => 'control-label']) !!}
                                    {!! Form::select('ticket_id', $event->tickets->pluck('title','id'), null, ['class' => 'form-control', 'id' => 'edit_ticket_id', 'placeholder' => 'Nessun collegamento']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('edit_position_x', 'Posizione X %', ['class' => 'control-label']) !!}
                                    {!! Form::number('position_x', null, ['class' => 'form-control', 'id' => 'edit_position_x', 'min' => 0, 'max' => 100, 'step' => '0.1']) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('edit_position_y', 'Posizione Y %', ['class' => 'control-label']) !!}
                                    {!! Form::number('position_y', null, ['class' => 'form-control', 'id' => 'edit_position_y', 'min' => 0, 'max' => 100, 'step' => '0.1']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('edit_rows', 'Numero file', ['class' => 'control-label']) !!}
                                    {!! Form::number('rows', null, ['class' => 'form-control', 'id' => 'edit_rows', 'min' => 1, 'max' => 50]) !!}
                                    <p class="help-block">Modifica la griglia solo se non hai ancora venduto posti in questa zona.</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('edit_cols', 'Posti per fila', ['class' => 'control-label']) !!}
                                    {!! Form::number('cols', null, ['class' => 'form-control', 'id' => 'edit_cols', 'min' => 1, 'max' => 50]) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('edit_start_row_label', 'Prima fila', ['class' => 'control-label']) !!}
                                    {!! Form::text('start_row_label', null, ['class' => 'form-control', 'id' => 'edit_start_row_label', 'maxlength' => 2]) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('edit_start_col_number', 'Primo posto n°', ['class' => 'control-label']) !!}
                                    {!! Form::number('start_col_number', null, ['class' => 'form-control', 'id' => 'edit_start_col_number', 'min' => 0, 'max' => 9999]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer mt15 text-right">
                            {!! Form::submit('Salva modifiche zona', ['class'=>"btn btn-success"]) !!}
                        </div>
                        {!! Form::close() !!}

                        {{-- Anteprima grafica mappa (admin) --}}
                        <hr>
                        <h4>Anteprima mappa (trascina le aree)</h4>
                        @php
                            $hasBg = !empty($currentMap->background_image_path);

                            if ($hasBg) {
                                // Modalità "piantina di sfondo": area un po' più alta
                                $mapHeight = 700;
                                $seatMapBgStyle = 'position: relative; height: '.$mapHeight.'px; width:900px; max-width:100%; border: 1px dashed #d1d5db; background:#f9fafb; margin:0 auto 10px auto;';
                                $seatMapBgStyle .= " background-image:url('".$currentMap->background_image_path."'); background-size:contain; background-position:center; background-repeat:no-repeat;";
                            } else {
                                // Modalità standard originale (senza immagine di sfondo)
                                $mapHeight = 520;
                                $seatMapBgStyle = 'position: relative; height: '.$mapHeight.'px; width:900px; max-width:100%; border: 1px dashed #d1d5db; background:#f9fafb; margin:0 auto 10px auto;';
                            }
                        @endphp
                        <div id="seat-map-admin" style="{{ $seatMapBgStyle }}">
                            <?php foreach($currentMap->zones as $zone): ?>
                                <?php
                                    // calcolo dimensioni proporzionali al numero di posti della zona
                                    $groupedSeats = $zone->seats->groupBy('row_label');
                                    $rowCount = max(1, $groupedSeats->count());
                                    $maxCols = 1;
                                    foreach ($groupedSeats as $rowSeats) {
                                        $maxCols = max($maxCols, $rowSeats->count());
                                    }

                                    if ($hasBg) {
                                        // Con piantina di sfondo: slot più compatti per far entrare molti posti
                                        $cellW = 16;
                                        $cellH = 20;
                                        $baseW = 32;
                                        $baseH = 32;
                                    } else {
                                        // Modalità standard: valori più grandi e comodi da cliccare
                                        $cellW = 28;
                                        $cellH = 32;
                                        $baseW = 40;
                                        $baseH = 40;
                                    }

                                    $boxW = $baseW + $maxCols * $cellW;
                                    $boxH = $baseH + $rowCount * $cellH;

                                    $top = is_null($zone->position_y) ? 5 : $zone->position_y;
                                    $left = is_null($zone->position_x) ? 5 : $zone->position_x;
                                ?>
                                <div class="seat-zone-admin"
                                     data-id="{{ $zone->id }}"
                                     style="position:absolute; top:{{ $top }}%; left:{{ $left }}%; width:{{ $boxW }}px; height:{{ $boxH }}px; border-radius:8px; background:{{ $zone->color ?: '#9ca3af' }}; opacity:0.9; color:#fff; display:flex; align-items:center; justify-content:center; font-size:10px; cursor:move; overflow:hidden; text-align:center;">
                                    {{ $zone->name }}
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-muted">
                            Suggerimento: trascina i riquadri colorati per posizionarli; poi usa <strong>“Salva modifiche zona”</strong> per applicare le nuove coordinate.
                        </p>
                    @endif
                </div>

                <div class="tab-pane {{$tab == 'dates' ? 'active' : ''}}" id="dates">
                    @include('ManageEvent.Partials.EventDates', ['event' => $event])
                </div>
            <!--/ tab content -->
        </div>
    </div>
@stop
