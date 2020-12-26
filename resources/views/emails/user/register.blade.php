@component('mail::message')
# Bonjour {{ $user->first_name }} {{  $user->last_name  }}

Votre compte Rest-Pass a été crée avec succés.

@component('mail::panel')
### Vos informations de connexion
* N° de dossier : {{ $user->number }}
* Mot de passe par defaut : bienvenue
@endcomponent
## Veuillez changer votre mot de passe pour plus de sécurité.

Merci,<br>
{{ "Resto Pass - Université de Thiès" }}
@endcomponent
