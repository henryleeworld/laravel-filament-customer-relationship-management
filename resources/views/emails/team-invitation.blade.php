<x-mail::message>
{{ __('You have been invited to join :app_name', ['app_name' => config('app.name')]) }}

{{ __('To accept the invitation - click on the button below and create an account:') }}

<x-mail::button :url="$acceptUrl">
{{ __('Create Account') }}
</x-mail::button>

{{ __('If you did not expect to receive an invitation to this team, you may discard this email.') }}
</x-mail::message>
