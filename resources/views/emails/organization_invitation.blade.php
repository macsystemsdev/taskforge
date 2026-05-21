<h1>
    Organization Invitation
</h1>

<p>
    You were invited to join
    {{ $invitation->organization->name }}
</p>

<a href="{{ route('invitations.accept', $invitation->token) }}">
    Accept Invitation
</a>

<a href="{{ route('invitations.reject', $invitation->token) }}">
    Accept Invitation
</a>