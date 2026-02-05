@extends('en.Emails.Layouts.Master')

@section('message_content')
    <div>
        Hallo,<br><br>
        Um Dein Passwort zurückzusetzen kannst Du dieses Formular ausfüllen: {{ route('password.reset', ['token' => $token]) }}.
        <br><br><br>
        Danke,<br>
       	EventiOne Team
    </div>
@stop
