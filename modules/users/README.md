The "Users" module
==================

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
disabled and a message with a key to unlock its account is sent to the user's email address.

Une fois le message envoyé et durant une heure, toutes les demandes de connexion échoueront sans
pour autant envoyer de message. Passé ce délai les compteurs sont remis à zéro.


	`failed_login_count` (int) the number of successive failed attempts. Cleared when the
	user successfully login.
	
	`failed_login_time` (int) Time of the last failed login.	
	
	`login_unlock_token` (int) Derivative salted token of the key send by email for the user to unlock
	its account.
	
	`login_unlock_time` (int) Time when the login will be unlocked.
	
	
	
	
Un système d'authentication sécurisé
------------------------------------
