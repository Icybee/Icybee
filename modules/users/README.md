The "Users" module
==================

Account security
----------------

Pour des raisons de sécuté le mot de passe n'est jamais envoyé par e-mail à l'utilisateur qui crée
son compte ou demande un mot de passe parce qu'il a oublié le sien et ne parvient plus à se
connecter. À la place, un ticket de connexion exceptionnel est envoyé à son adresse e-mail. Ce
ticket, à usage unique et limité dans le temps, permet à l'utilisateur de se connecter
immédiatement, ce qui lui permet de modifier son mot de passe par exemple.

Pour encore plus de sécurité, l'adresse IP de la requête est également consignée, ce qui réduit
encore un peu plus les risques d'interceptions.   

Preventing brute force login
----------------------------

In order to prevent brute force login, each failed attempt is counted in the metas of the target
user account. When the number of failed attempts reaches a limit (e.g. 10) the user account is
locked and a message with a key to unlock it is sent to the user's email address.

Once the message has been sent all subsequent connection requests will fail during an hour. After
this delay counter are reseted.

The following metas properties are used for the process:

- (int) `failed_login_count`: The number of successive failed attempts. Cleared when the
user successfully login.
- (int) `failed_login_time`: Time of the last failed login.
- (string) `login_unlock_token`: Derivative salted token of the key sent by email for the user
to unlock its account.
- (int) `login_unlock_time`: Time at which login is unlocked.