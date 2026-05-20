@component('mail::message')
# Invitation

You’ve been invited to join **{{ $invitation->organization->name }}**

@component('mail::button', ['url' => url('/invitations/'.$invitation->token)])
Accept Invitation
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent