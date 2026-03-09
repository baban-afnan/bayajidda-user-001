<x-mail::message>
# Welcome to {{ config('app.name') }}, {{ $user->first_name ?? 'Valued Customer' }}!

We are thrilled to have you on board.

As a token of our appreciation, a welcome bonus of **₦{{ number_format($bonusAmount, 2) }}** has been credited to your wallet.

Enjoy using our services, create your transaction history, and please let us know if you need any assistance setting up your complete process.

<x-mail::button :url="route('dashboard')">
Go to Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
